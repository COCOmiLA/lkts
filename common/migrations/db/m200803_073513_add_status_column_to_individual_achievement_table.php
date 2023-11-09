<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200803_073513_add_status_column_to_individual_achievement_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%individual_achievement}}', 'status', $this->integer()->null());
        Yii::$app->db->schema->refresh();
        \common\modules\abiturient\models\IndividualAchievement::updateAll([
            'status' => 1
        ]);
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%individual_achievement}}', 'status');
    }
}
