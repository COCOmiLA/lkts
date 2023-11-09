<?php

namespace common\services\abiturientController\sandbox;

use common\models\EmptyCheck;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\AddressData;
use common\modules\abiturient\models\PersonalData;
use common\services\abiturientController\BaseService;
use Yii;
use yii\validators\EmailValidator;

class PartialApplicationSavingService extends BaseService
{
    




    public function getAbiturientQuestionaryById(?int $questionaryId): ?AbiturientQuestionary
    {
        return AbiturientQuestionary::findOne($questionaryId);
    }

    




    public function validateEmail(AbiturientQuestionary $questionary): string
    {
        $user = $questionary->user;
        $userEmail = $this->request->post('user_email');

        if (
            EmptyCheck::isEmpty($userEmail) ||
            $user->email == $userEmail
        ) {
            return '';
        }

        $error = '';
        $validator = new EmailValidator();
        if ($validator->validate($userEmail, $error)) {
            $user->email = $userEmail;
            $user->save();
        }

        return $error;
    }

    




    public function validatePersonalData(AbiturientQuestionary $questionary): string
    {
        $personalData = PersonalData::findOne(['questionary_id' => $questionary->id]);
        $personalData->load($this->request->post());
        if ($personalData->save()) {
            return '';
        }

        return !empty($personalData->errors) ? implode(', ', array_merge(...array_values($personalData->errors))) : Yii::t(
            'sandbox/moderate/all',
            'Текст неизвестной ошибки, когда не удалось сохранить модель персональных данных: `Неизвестная ошибка`'
        );
    }

    





    public function validateAddressData(AbiturientQuestionary $questionary, string $addressType): string
    {
        $addressData = null;
        if ($addressType == 'registration') {
            $addressData = AddressData::findOne(['questionary_id' => $questionary->id]);
        } elseif ($addressType == 'actual') {
            $addressData = $questionary->getOrCreateActualAddressData();
        }

        $addressData->load($this->request->post());

        
        $this->processAddressDataFromPost($addressData);
        if ($addressData->save()) {
            return '';
        }

        return !empty($addressData->errors) ? implode(', ', array_merge(...array_values($addressData->errors))) : Yii::t(
            'sandbox/moderate/all',
            'Текст неизвестной ошибки, когда не удалось сохранить модель адреса: `Неизвестная ошибка`'
        );
    }

    




    private function processAddressDataFromPost(AddressData $addressData): AddressData
    {
        $addressData->processKLADRCode();

        $addressData->cleanUnusedAttributes();

        if ($addressData->area_id == "null") {
            $addressData->area_id = null;
        }

        if (
            $addressData->country != null &&
            $addressData->country->ref_key != $this->configurationManager->getCode('russia_guid')
        ) {
            $addressData->not_found = true;
        }

        return $addressData;
    }
}
