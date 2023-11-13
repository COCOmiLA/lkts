<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m180413_073942_moderate_admission_campaign extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable(
            'moderate_admission_campaign',
            [
                'id' => $this->primaryKey(),
                'rbac_auth_assignment_user_id' => $this->integer(11)->notNull(),
                'application_type_id' => $this->integer(11)->notNull(),
            ]
        );

        $this->addForeignKey(
            'fk_mac_at_id',
            'moderate_admission_campaign',
            'application_type_id',
            'application_type',
            'id'
        );

        \Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropForeignKey(
            'fk_mac_at_id',
            'moderate_admission_campaign'
        );

        $this->dropTable('moderate_admission_campaign');

        \Yii::$app->db->schema->refresh();
    }
}
