<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210414_160829_create_portal_database_version_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%portal_database_version}}', [
            'id' => $this->primaryKey(),
            'version' => $this->string(255)->notNull(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ]);
        
        $migrationVersion = new common\components\migrations\VersionMigration();
        $migrationVersion->insertRow('0.0.13.1');
        sleep ( 10); 
    }

    


    public function safeDown()
    {
        $this->dropTable('{{%portal_database_version}}');
    }
}
