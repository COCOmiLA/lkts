<?php

namespace backend\models\search;

use Throwable;
use Yii;
use yii\base\Model;




class UserDuplesSearchModel extends Model
{
    public $first_name;
    public $last_name;
    public $patronimyc;
    public $birth_date;

    public $found_duples = [];

    public function attributeLabels()
    {
        return [
            'first_name' => 'Имя',
            'last_name' => 'Фамилия',
            'patronimyc' => 'Отчество',
            'birth_date' => 'Дата рождения',
        ];
    }

    public function rules()
    {
        return [
            [['first_name', 'last_name', 'patronimyc', 'birth_date'], 'string'],
        ];
    }

    public function getFormated_birthdate()
    {
        return date("Y-m-d", strtotime($this->birth_date));
    }

    public function load_and_search(array $post_data): UserDuplesSearchModel
    {
        $this->load($post_data);
        try {
            $this->found_duples = Yii::$app->authentication1CManager->getAbiturientCodeDoubles(
                $this->formated_birthdate,
                $this->last_name,
                $this->first_name,
                $this->patronimyc
            );
        } catch (Throwable $e) {
            Yii::error("Не удалось найти физ лиц для сопоставления: {$e->getMessage()}", 'GetAbiturientCodeDoubles');
            $this->found_duples = [];
        }
        return $this;
    }
}