<?php

namespace common\modules\abiturient\models\bachelor;

use backend\components\ApplicationTypeHistoryTrait;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;













class ApplicationTypeSettings extends ActiveRecord
{
    use ApplicationTypeHistoryTrait;

    


    public static function tableName()
    {
        return '{{%application_type_settings}}';
    }

    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    


    public function rules()
    {
        return [
            [
                [
                    'application_type_id',
                    'created_at',
                    'updated_at'
                ],
                'integer'
            ],
            [

                'value',
                'boolean'
            ],
            [
                'name',
                'string',
                'max' => 255
            ],
            [
                ['application_type_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => ApplicationType::class,
                'targetAttribute' => ['application_type_id' => 'id']
            ],
        ];
    }

    




    public function getApplicationType()
    {
        return $this->hasOne(ApplicationType::class, ['id' => 'application_type_id']);
    }
}
