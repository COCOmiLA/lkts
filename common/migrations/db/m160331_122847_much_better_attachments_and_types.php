<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160331_122847_much_better_attachments_and_types extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%attachment_type}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(1000)->notNull(),
            'required' => $this->smallInteger()->defaultValue(0),
            'related_entity' => $this->string(100)->notNull(),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
        $this->dropColumn('{{%attachment}}', 'attachment_type');
        $this->addColumn('{{%attachment}}', 'attachment_type_id', $this->integer()->notNull());
        $this->addColumn('{{%attachment}}', 'extension', $this->string(255));

        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%attachment}}', 'attachment_type', $this->string(100)->notNull());
        $this->dropColumn('{{%attachment}}', 'attachment_type_id');
        $this->dropColumn('{{%attachment}}', 'extension');
        
        $this->dropTable('{{%attachment_type}}');
        Yii::$app->db->schema->refresh();
    }
}
