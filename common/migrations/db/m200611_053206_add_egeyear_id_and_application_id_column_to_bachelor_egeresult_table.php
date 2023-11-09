<?php

use common\components\Migration\MigrationWithDefaultOptions;







class m200611_053206_add_egeyear_id_and_application_id_column_to_bachelor_egeresult_table extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        $this->addColumn('{{%bachelor_egeresult}}', 'egeyear', $this->string());
        $this->addColumn('{{%bachelor_egeresult}}', 'application_id', $this->integer());

        
        $this->createIndex(
            '{{%idx-bachelor_egeresult-application_id}}',
            '{{%bachelor_egeresult}}',
            'application_id'
        );

        
        $this->addForeignKey(
            '{{%fk-bachelor_egeresult-application_id}}',
            '{{%bachelor_egeresult}}',
            'application_id',
            '{{%bachelor_application}}',
            'id',
            'CASCADE'
        );
    }

    


    public function down()
    {
        $this->dropForeignKey(
            '{{%fk-bachelor_egeresult-application_id}}',
            '{{%bachelor_egeresult}}'
        );

        
        $this->dropIndex(
            '{{%idx-bachelor_egeresult-application_id}}',
            '{{%bachelor_egeresult}}'
        );

        $this->dropColumn('{{%bachelor_egeresult}}', 'application_id');
        $this->dropColumn('{{%bachelor_egeresult}}', 'egeyear');
    }
}
