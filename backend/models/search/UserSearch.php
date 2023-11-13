<?php

namespace backend\models\search;

use common\components\LikeQueryManager;
use common\models\EmptyCheck;
use common\models\query\ActiveRecordDataProvider;
use common\models\User;
use common\models\UserProfile;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\PersonalData;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;




class UserSearch extends User
{
    
    public $pageSize;

    
    public $fio;

    
    public $group_name;

    
    public $admission_campaign;

    
    public $block_status;

    
    public $is_archive;
    public $role;

    


    public function rules()
    {
        return [
            [
                [
                    'id',
                    'status',
                    'created_at',
                    'updated_at',
                    'logged_at',
                    'is_archive'
                ],
                'integer'
            ],
            [
                [
                    'admission_campaign',
                    'fio',
                    'group_name',
                    'pageSize',
                    'role',
                ],
                'string'
            ],
            [
                [
                    'username',
                    'auth_key',
                    'password_hash',
                    'password_reset_token',
                    'email',
                    'block_status',
                ],
                'safe'
            ],
        ];
    }

    



    public function search($params)
    {
        $query = User::find()
            
            
            ->joinWith(['rawApplications'])
            ->joinWith(['rawApplications.type.rawCampaign'])
            ->joinWith('rawApplications.rawSpecialities.speciality as speciality')
            ->joinWith(['rbacAuthAssignment rbac_auth_assignment']);

        if ($this->load($params) && $this->validate()) {
            $query->andFilterWhere([
                'user.id' => $this->id,
                'user.status' => $this->status,
                'user.is_archive' => $this->is_archive,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'logged_at' => $this->logged_at
            ]);

            $query->andFilterWhere([LikeQueryManager::getActionName(), 'admission_campaign.name', trim((string)$this->admission_campaign)]);
            $query->andFilterWhere([LikeQueryManager::getActionName(), 'speciality.group_name', trim((string)$this->group_name)]);

            $query->andFilterWhere([LikeQueryManager::getActionName(), 'username', trim((string)$this->username)])
                ->andFilterWhere([LikeQueryManager::getActionName(), 'auth_key', $this->auth_key])
                ->andFilterWhere([LikeQueryManager::getActionName(), 'password_hash', $this->password_hash])
                ->andFilterWhere([LikeQueryManager::getActionName(), 'password_reset_token', $this->password_reset_token])
                ->andFilterWhere([LikeQueryManager::getActionName(), 'email', trim((string)$this->email)]);
            if ($this->role) {
                $query
                    ->andWhere(['rbac_auth_assignment.item_name' => $this->role]);
            }
        }

        if (!EmptyCheck::isEmpty($this->block_status)) {
            $tn = BachelorApplication::tableName();
            $query->andFilterWhere(['=', "{$tn}.block_status", $this->block_status]);
        }

        if (!EmptyCheck::isEmpty($this->fio)) {
            $query = UserSearch::filterByFio($query, $this->fio);
        }

        if (empty($this->pageSize)) {
            $this->pageSize = 50;
        }
        $provider = new ActiveRecordDataProvider([
            'query' => $query,
            'primary_column' => 'user.id',
            'sort' => ['route' => 'user/index'],
            'pagination' => [
                'pagesize' => (int)$this->pageSize,
                'route' => 'user/index',
            ]
        ]);

        $provider->sort->defaultOrder = ['id' => SORT_ASC];
        $provider->sort->attributes['status'] = [
            'asc' => ['user.status' => SORT_ASC],
            'desc' => ['user.status' => SORT_DESC]
        ];
        $provider->sort->attributes['created_at'] = [
            'asc' => ['user.created_at' => SORT_ASC],
            'desc' => ['user.created_at' => SORT_DESC]
        ];
        $provider->sort->attributes['role'] = [
            'asc' => ['rbac_auth_assignment.item_name' => SORT_ASC],
            'desc' => ['rbac_auth_assignment.item_name' => SORT_DESC]
        ];
        return $provider;
    }

    





    private static function filterByFio(ActiveQuery $query, string $fio): ActiveQuery
    {
        $fioList = array_filter(
            array_map(
                function (string $fioPart): string {
                    return trim($fioPart);
                },
                explode(' ', $fio)
            ),
            function (string $fioPart): bool {
                return !empty($fioPart);
            }
        );
        if (count($fioList) < 1) {
            return $query;
        }

        $conditions = array_filter([
            UserSearch::filterByFioInUserTable($fioList),
            UserSearch::filterByFioInUserProfileTable($query, $fioList),
            UserSearch::filterByFioInPersonalDataTable($query, $fioList)
        ]);

        if ($conditions) {
            $query->andWhere(['or', ...$conditions]);
        }

        return $query;
    }

    




    private static function filterByFioInUserTable(array $fioList): array
    {
        $conditions = [];
        $tn = User::tableName();
        foreach ($fioList as $fioPart) {
            $conditions[] = [LikeQueryManager::getActionName(), "{$tn}.username", $fioPart];
        }

        return ['and', ...$conditions];
    }

    





    private static function filterByFioInUserProfileTable(ActiveQuery $query, array $fioList): array
    {
        return UserSearch::filterByFioInJoinTable(
            $query,
            $fioList,
            'userProfile',
            UserProfile::tableName()
        );
    }

    





    private static function filterByFioInPersonalDataTable(ActiveQuery $query, array $fioList): array
    {
        return UserSearch::filterByFioInJoinTable(
            $query,
            $fioList,
            'rawAbiturientQuestionary.personalData',
            PersonalData::tableName()
        );
    }

    







    private static function filterByFioInJoinTable(
        ActiveQuery $query,
        array       $fioList,
        string      $joinPath,
        string      $tableName
    ): array {
        $columns = [
            'firstname',
            'middlename',
            'lastname',
        ];

        $query = $query->joinWith($joinPath);

        $conditions = [];
        foreach ($fioList as $fioPart) {
            $column_conditions = [];
            foreach ($columns as $column) {
                $column_conditions[] = [
                    LikeQueryManager::getActionName(), "{$tableName}.{$column}", $fioPart
                ];
            }
            $conditions[] = ['or', ...$column_conditions];
        }

        return ['and', ...$conditions];
    }
}
