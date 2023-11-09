<?php







namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

class ManageAC extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%moderate_admission_campaign}}';
    }

    public function rules()
    {
        return [
            [['rbac_auth_assignment_user_id', 'application_type_id'], 'required'],
            [['rbac_auth_assignment_user_id', 'application_type_id'], 'integer'],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'id' => Yii::t('backend', 'ID'),
            'rbac_auth_assignment_user_id' => 'Id модератора',
            'application_type_id' => 'Id приемной кампании',
        ];
    }
}