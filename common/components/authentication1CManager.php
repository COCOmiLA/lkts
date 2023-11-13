<?php

namespace common\components;

use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\dictionary\DocumentType;
use common\models\EmptyCheck;
use common\models\EntityForDuplicatesFind;
use common\models\ToAssocCaster;
use common\modules\abiturient\models\AbiturientQuestionary;
use frontend\modules\user\models\AccessForm;
use Yii;

class authentication1CManager extends \yii\base\Component
{

    public function checkAbiturientRegistration($passport_number, $passport_series,
                                                $lastname, $firstname, $middlename, $email, $birthday)
    {
        $result = Yii::$app->soapClientAbit->load("GetAbiturientCode",
            [
                'Series' => str_replace(' ', '', $passport_series),
                'Number' => str_replace(' ', '', $passport_number),
            ]);

        if ($result === false) {
            return false;
        }
        return EmptyCheck::isEmpty((string)$result->return->Code);
    }

    



    public function getAbiturientCodesByAnyDocumentsData(AbiturientQuestionary $questionary): array
    {
        $passports = $questionary->passportData;
        $personalData = $questionary->personalData;

        $data_to_check = array_map(function ($p) {
            return [
                'series' => $p->series,
                'number' => $p->number,
            ];
        }, $passports);

        if ($personalData) {
            $data_to_check[] = [
                'series' => $personalData->passport_series,
                'number' => $personalData->passport_number,
            ];
        }

        $data_to_check = array_unique($data_to_check, SORT_REGULAR);
        $result = [];
        foreach ($data_to_check as $data) {
            $data = $this->getAbiturientCode($data['number'], $data['series']);
            if ($data) {
                $result[] = $data;
            }
        }

        return array_values(array_unique($result));
    }

    




    public function getAbiturientCode($passport_number, $passport_series)
    {
        $result = false;
        try {
            $result = Yii::$app->soapClientAbit->load(
                'GetAbiturientCode',
                [
                    'Series' => str_replace(' ', '', $passport_series),
                    'Number' => str_replace(' ', '', $passport_number),
                ]
            );
        } catch (\Throwable $e) {
            Yii::error("Ошибка вызова метода GetAbiturientCode: {$e->getMessage()}", 'GetAbiturientCode');
        }

        if (!$result || EmptyCheck::isEmpty((string)$result->return->Code)) {
            return false;
        }
        return [
            'AbiturientCode' => (string)$result->return->Code,
            'DeletionMark' => (bool)$result->return->DeletionMark,
        ];
    }

    







    public function getAbiturientCodeDoubles(string $birthDate, string $lastName, string $firstName, string $secondName): array
    {
        $formatted_date = empty(str_replace(' ', '', $birthDate)) ? '0001-01-01' : str_replace(' ', '', date('Y-m-d', strtotime($birthDate)));
        $result = Yii::$app->soapClientAbit->load(
            'GetAbiturientCodeDoubles',
            [
                'LastName' => trim((string)$lastName),
                'FirstName' => trim((string)$firstName),
                'SecondName' => trim((string)$secondName),
                'BirthDate' => $formatted_date,
            ]
        );

        if (!$result || !isset($result->return->AbiturientDoubles)) {
            return [];
        }

        if (!is_array($result->return->AbiturientDoubles)) {
            $result->return->AbiturientDoubles = [$result->return->AbiturientDoubles];
        }
        $buffer = $result->return->AbiturientDoubles;

        if (!$buffer) {
            return [];
        }
        
        
        return array_map(function ($item) {
            return ToAssocCaster::getAssoc($item);
        }, array_values(array_filter($buffer, function ($item) use ($formatted_date) {
            return !$formatted_date || $formatted_date == '0001-01-01' || ($item->Birthdate ?? '') == $formatted_date;
        })));
    }

    





    public function getAbiturientCodeAlt(AccessForm $accessForm)
    {
        $type = DocumentType::findOne($accessForm->documentTypeId);

        $result = false;
        try {
            $result = Yii::$app->soapClientAbit->load(
                'GetAbiturientCodeAlt',
                [
                    'LastName' => trim((string)$accessForm->lastname),
                    'FirstName' => trim((string)$accessForm->firstname),
                    'SecondName' => trim((string)$accessForm->secondname),
                    'BirthDate' => empty(str_replace(' ', '', $accessForm->birth_date)) ? '0001-01-01' : str_replace(' ', '', date('Y-m-d', strtotime($accessForm->birth_date))),
                    'Documents' => [
                        'Document' => [
                            'DocType' => $type->code,
                            'DocCategory' => 'Passport',
                            'DocNumber' => $accessForm->passportNumber,
                            'DocSeries' => $accessForm->passportSeries,
                            'DocumentTypeRef' => ReferenceTypeManager::GetReference($type)
                        ]
                    ]
                ]
            );
        } catch (\Throwable $e) {
            return null;
        }

        if ($result === false) {
            return false;
        }

        if ($result->return->UniversalResponse->Complete == 0) {
            Yii::$app->getSession()->setFlash('alert', [
                'body' => $result->return->UniversalResponse->Description,
                'options' => ['class' => 'alert-danger']
            ]);
            return null;
        }

        if (!isset($result->return->AbitCode)) {
            return null;
        }
        if ($result->return->AbitCode == "") {
            return null;
        } else {
            return $result->return->AbitCode;
        }
    }

    public function getAbiturientDoublesByFullInfo(EntityForDuplicatesFind $entity): array
    {
        $result = [];
        try {
            foreach ($entity->buildTo1C() as $request_data) {
                try {
                    $response = Yii::$app->soapClientAbit->load(
                        'GetAbiturientCodePassFIOBDate',
                        $request_data
                    );
                    $tmp = ($response->return ?? null)->AbiturientDoubles ?? [];
                    if (!is_array($tmp)) {
                        $tmp = [$tmp];
                    }
                    $result = [...$result, ...$tmp];
                } catch (\Throwable $e) {
                }
            }

        } catch (\Throwable $e) {
            return [];
        }
        return $result;
    }
}
