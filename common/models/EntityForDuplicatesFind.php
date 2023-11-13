<?php

namespace common\models;

use common\components\ReferenceTypeManager\ReferenceTypeManager;

class EntityForDuplicatesFind extends \yii\base\BaseObject
{
    private $firstname;
    private $patronymic;
    private $lastname;
    private $birth_date;
    private $snils;
    private $documents = [];

    public function __construct(
        string $firstname,
        string $lastname,
        string $patronymic,
        string $birth_date,
        ?string $snils,
        array  $documents
    )
    {
        parent::__construct();

        $this->firstname = trim((string)$firstname);
        $this->lastname = trim((string)$lastname);
        $this->patronymic = trim((string)$patronymic);
        $this->snils = $snils;

        $this->birth_date = empty(str_replace(' ', '', (string)$birth_date)) ? '0001-01-01' : str_replace(' ', '', date('Y-m-d', strtotime($birth_date)));

        $this->documents = $documents;
    }

    public function buildTo1C(): array
    {
        $result = [];
        foreach ($this->documents as $raw_doc) {
            $result[] = [
                'Series' => (string)$raw_doc['series'],
                'Number' => (string)$raw_doc['number'],
                'DocumentTypeRef' => ReferenceTypeManager::GetReference($raw_doc['type']),
                'Snils' => (string)$this->snils,
                'BirthDate' => (string)$this->birth_date,
                'FirstName' => (string)$this->firstname,
                'LastName' => (string)$this->lastname,
                'SecondName' => (string)$this->patronymic,
            ];
        }
        return $result;
    }
}