<?php








namespace backend\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class RBACAuthAssignment extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%rbac_auth_assignment}}';
    }

    public function rules()
    {
        return [
            [
                [
                    'user_id',
                    'item_name',
                ],
                'string',
                'max' => 64
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'item_name' => 'Роль',
            'user_id' => 'Id пользователя',
        ];
    }

    






    public static function getRolesByUsersIds(array $usersIds): array
    {
        $assignments = RBACAuthAssignment::find()
            ->where(['IN', 'user_id', $usersIds])
            ->all();

        if (!$assignments) {
            return [];
        }

        return ArrayHelper::map($assignments, 'user_id', 'item_name');
    }
}
