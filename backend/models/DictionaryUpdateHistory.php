<?php

namespace backend\models;

use common\models\errors\RecordNotValid;
use Yii;
use yii\db\Query;

class DictionaryUpdateHistory extends \yii\db\ActiveRecord
{
    public function rules()
    {
        return [
            [
                ['method_name', 'updated_at'],
                'required',
            ],
            [
                'method_name',
                'string',
            ],
            [
                'updated_at',
                'integer',
            ]

        ];
    }

    public static function tableName()
    {
        return '{{%dictionary_update_history}}';
    }

    public static function setUpdateTime(string $method_name, int $time)
    {
        $record = DictionaryUpdateHistory::find()->where(['method_name' => $method_name])->one();
        if (!$record) {
            $record = new DictionaryUpdateHistory();
            $record->method_name = $method_name;
        }
        $record->updated_at = $time;
        if (!$record->save()) {
            throw new RecordNotValid($record);
        }
    }

    public static function hasUpdatedDictionariesAfterVersionMigrated()
    {
        if (!Yii::$app->db->schema->getTableSchema('system_db_migration') || !Yii::$app->db->schema->getTableSchema(DictionaryUpdateHistory::tableName())) {
            return true; 
        }
        $latest_version_migration_timestamp = (new Query())
            ->select(['MAX(apply_time)'])
            ->from(['migrations' => 'system_db_migration'])
            ->where(['LIKE', 'version', 'version_migration']);
        return DictionaryUpdateHistory::find()->where(['>', 'updated_at', $latest_version_migration_timestamp])->exists();
    }
}
