<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210720_151026_add_new_column_to_portal_database_version_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%portal_database_version}}', 'subversion1', $this->integer()->null());
        $this->addColumn('{{%portal_database_version}}', 'subversion2', $this->integer()->null());
        $this->addColumn('{{%portal_database_version}}', 'subversion3', $this->integer()->null());
        $this->addColumn('{{%portal_database_version}}', 'subversion4', $this->integer()->null());
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%portal_database_version}}', 'subversion1');
        $this->dropColumn('{{%portal_database_version}}', 'subversion2');
        $this->dropColumn('{{%portal_database_version}}', 'subversion3');
        $this->dropColumn('{{%portal_database_version}}', 'subversion4');
    }
}
