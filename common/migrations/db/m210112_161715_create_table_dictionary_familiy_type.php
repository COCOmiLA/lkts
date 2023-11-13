<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210112_161715_create_table_dictionary_familiy_type extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%dictionary_family_type}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'uid' => $this->string()->notNull(),
            'ref_id' => $this->string()->notNull(),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
            'archive' => $this->boolean()
        ]);
    }

    


    public function safeDown()
    {
        $this->dropTable('{{%dictionary_family_type}}');
    }
}
