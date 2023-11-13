<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210118_055144_add_contract_guid_column_to_bachelor_speciality_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%bachelor_speciality}}', 'paid_contract_guid', $this->string());

        Yii::$app->db->schema->refresh();

    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%bachelor_speciality}}', 'paid_contract_guid');
        Yii::$app->db->schema->refresh();

    }
}
