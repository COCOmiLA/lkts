<?php

namespace frontend\models;


use yii\base\Model;




class UpdateContactForm extends Model
{
    public $user;
    public $email;
    public $main_phone;
    public $secondary_phone;

    


    public function rules()
    {
        return [
            [
                'user',
                'required'
            ],
            [
                'email',
                'email'
            ],
            [
                [
                    'main_phone',
                    'secondary_phone'
                ], 'string',
                'max' => 50
            ],
        ];
    }

    


    public function save(): array
    {
        if (!$this->user || !$this->validate()) {
            return $this->errors;
        }

        if (
            $this->email &&
            $this->user->email != $this->email
        ) {
            $this->user->email = $this->email;
            if (!$this->user->save(true, ['email'])) {
                return $this->user->errors;
            }
        }
        $abiturientQuestionary = $this->user->abiturientQuestionary;
        if (
            $this->main_phone &&
            $abiturientQuestionary->personalData->main_phone != $this->main_phone
        ) {
            $abiturientQuestionary->personalData->main_phone = $this->main_phone;
            if (!$abiturientQuestionary->personalData->save(true, ['main_phone'])) {
                return $abiturientQuestionary->personalData->errors;
            }
        }
        if (
            $this->secondary_phone &&
            $abiturientQuestionary->personalData->secondary_phone != $this->secondary_phone
        ) {
            $abiturientQuestionary->personalData->secondary_phone = $this->secondary_phone;
            if (!$abiturientQuestionary->personalData->save(true, ['secondary_phone'])) {
                return $abiturientQuestionary->personalData->errors;
            }
        }

        return [];
    }
}
