<?php







namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

class RBACAuthItem extends ActiveRecord
{
    const ABITURIENT = 'abiturient';
    const ADMINISTRATOR = 'administrator';
    const LOGIN_TO_BACKEND = 'loginToBackend';
    const MANAGER = 'manager';
    const STUDENT = 'student';
    const TEACHER = 'teacher';
    const USER = 'user';
    const VIEWER = 'viewer';

    public static function tableName()
    {
        return '{{%rbac_auth_item}}';
    }

    public function rules()
    {
        return [
            ['name', 'string', 'max' => 64],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'id' => Yii::t('backend', 'ID'),
            'name' => 'Роль',
        ];
    }
}