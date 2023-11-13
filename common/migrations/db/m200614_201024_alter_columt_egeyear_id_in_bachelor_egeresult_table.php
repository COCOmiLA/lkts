<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200614_201024_alter_columt_egeyear_id_in_bachelor_egeresult_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->dropForeignKey('fk_bachelor_egeresult_egeyear','bachelor_egeresult');
        $this->alterColumn('bachelor_egeresult', 'egeyear_id', $this->integer()->null());
    }

    


    public function safeDown()
    {
        echo "m200614_201024_alter_columt_egeyear_id_in_bachelor_egeresult_table cannot be reverted.\n";

        return false;
    }

    













}
