<?php

use common\components\Migration\SafeMigration;
use yii\db\Query;




class m230524_065013_recovery_entrant_tests extends SafeMigration
{
    private const CONDITION_TYPE_TN    = '{{%cget_condition_type}}';
    private const ENTRANCE_TEST_SET_TN = '{{%cget_entrance_test_set}}';

    


    public function safeUp()
    {
        foreach ($this->getExistingEntranceTestSets() as $entranceTestSet) {
            if (!empty($entranceTestSet['profile_ref_id'])) {
                $this->insert(
                    self::CONDITION_TYPE_TN,
                    [
                        'cget_entrance_test_set_id' => $entranceTestSet['id'],
                        'profile_reference_type_id' => $entranceTestSet['profile_ref_id'],
                    ]
                );
            }
            if (!empty($entranceTestSet['education_type_ref_id'])) {
                $this->insert(
                    self::CONDITION_TYPE_TN,
                    [
                        'cget_entrance_test_set_id' => $entranceTestSet['id'],
                        'dictionary_education_type_id' => $entranceTestSet['education_type_ref_id'],
                    ]
                );
            }
        }
    }

    


    public function safeDown()
    {
    }

    


    private function getExistingEntranceTestSets(): array
    {
        return (new Query())
            ->select([
                'id',
                'education_type_ref_id',
                'profile_ref_id'
            ])
            ->from(self::ENTRANCE_TEST_SET_TN)
            ->andWhere(['archive' => false])
            ->andWhere([
                'OR',
                ['IS NOT', 'profile_ref_id', null],
                ['IS NOT', 'education_type_ref_id', null],
            ])
            ->all();
    }
}
