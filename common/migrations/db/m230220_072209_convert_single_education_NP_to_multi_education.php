<?php

use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Query;




class m230220_072209_convert_single_education_NP_to_multi_education extends MigrationWithDefaultOptions
{
    private const TN_JUNCTION = '{{%bachelor_speciality_education_data}}';
    private const TN_SPECIALITY = '{{%bachelor_speciality}}';

    


    public function safeUp()
    {
        $existJunctions = (new Query())
            ->from(self::TN_JUNCTION)
            ->select([self::TN_JUNCTION . '.education_data_id']);

        $specialities = (new Query())
            ->from(self::TN_SPECIALITY)
            ->select([self::TN_SPECIALITY . '.education_id', self::TN_SPECIALITY . '.id'])
            ->andWhere(['IS NOT', self::TN_SPECIALITY . '.education_id', null])
            ->andWhere(['NOT IN', self::TN_SPECIALITY . '.education_id', $existJunctions])
            ->all();

        foreach ($specialities as $specialty) {
            $this->insert(
                self::TN_JUNCTION,
                [
                    'bachelor_speciality_id' => $specialty['id'],
                    'education_data_id' => $specialty['education_id'],
                ]
            );
        }
    }

    


    public function safeDown()
    {
    }
}
