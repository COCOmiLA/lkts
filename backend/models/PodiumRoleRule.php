<?php

namespace backend\models;

use Yii;
use yii\base\DynamicModel;
use yii\helpers\ArrayHelper;








class PodiumRoleRule extends \yii\db\ActiveRecord
{
    const ENABLE = true;
    const DISABLE = false;

    


    public static function tableName()
    {
        return '{{%podium_role_rule}}';
    }

    


    public function rules()
    {
        return [
            [['rule'], 'boolean'],
            [['role'], 'string', 'max' => 255],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'role' => 'Роль',
            'rule' => 'Правило',
        ];
    }

    




    public static function updateRole(bool $deleteNotAvailableRole = true)
    {
        $allRoles = self::find()->all();
        $oldRoles = array_map(
            function ($role) {
                
                return $role->role;
            },
            $allRoles
        );
        $newRoles = RBACAuthItem::find()
            ->where(['type' => 1])
            ->andWhere(['not in', 'name', $oldRoles])
            ->all();

        if (!empty($newRoles)) {
            foreach ($newRoles as $role) {
                $newRoleRule = new PodiumRoleRule();
                $newRoleRule->role = $role->name;
                $newRoleRule->save();
            }
        }

        if ($deleteNotAvailableRole) {
            $availableRole = array_map(
                function ($role) {
                    
                    return $role->name;
                },
                RBACAuthItem::find()->all()
            );
            $rolesToDelete = self::find()
                ->where([
                    'not in',
                    'role',
                    $availableRole
                ])
                ->all();

            if (!empty($rolesToDelete)) {
                foreach ($rolesToDelete as $role) {
                    $role->delete();
                }
            }
        }
    }

    





    public static function setRoleRule($podiumRolesRules = [], $forumModel = null)
    {
        if (isset($forumModel) && !empty($podiumRolesRules)) {
            $hasError = false;
            $errorPodium = null;
            foreach ($podiumRolesRules as $podium) {
                $role = $podium->role;
                $podium->rule = $forumModel->$role;

                if ($podium->validate()) {
                    if (!$podium->save(false)) {
                        $hasError = true;
                        $errorPodium = $podium;
                        break;
                    }
                } else {
                    Yii::$app->session->setFlash('alert', [
                        'body' => 'Ошибка сохранения роли форума.',
                        'options' => ['class' => 'alert-danger']
                    ]);
                    Yii::error('Ошибка сохранения роли форума.' . PHP_EOL . print_r([
                            'forumModel' => $forumModel ?? '-',
                            'podium' => ArrayHelper::getValue($podium, 'id') ?? '-',
                            'errors' => ArrayHelper::getValue($podium, 'errors') ?? '-',
                        ], true));
                }
            }
            if ($hasError) {
                Yii::$app->session->setFlash('alert', [
                    'body' => 'Неизвестная ошибка сохранения роли форума.',
                    'options' => ['class' => 'alert-danger']
                ]);
                Yii::error('Неизвестная ошибка сохранения роли форума.' . PHP_EOL . print_r([
                        'forumModel' => $forumModel ?? '-',
                        'errors' => ArrayHelper::getValue($errorPodium, 'errors') ?? '-',
                        'errorPodium' => ArrayHelper::getValue($errorPodium, 'id') ?? '-',
                    ], true));
            } else {
                Yii::$app->session->setFlash('alert', [
                    'body' => 'Изменения прошли успешно.',
                    'options' => ['class' => 'alert-success']
                ]);
            }
        } else {
            Yii::$app->session->setFlash('alert', [
                'body' => 'Ошибка. Отсутствуют структуры для сохранения.',
                'options' => ['class' => 'alert-danger']
            ]);
            Yii::error('Ошибка. Отсутствуют структуры для сохранения.' . PHP_EOL . print_r([
                    'forumModel' => $forumModel ?? '-',
                    'podiumRolesRules' => empty($podiumRolesRules) ? '-' : $podiumRolesRules,
                ], true));
        }
    }

    


    public static function getAvailableRole()
    {
        $availableRole = self::findAll(['rule' => self::ENABLE]);
        if (empty($availableRole)) {
            return [];
        }

        return array_map(
            function ($role) {
                
                return $role->role;
            },
            $availableRole
        );
    }
}
