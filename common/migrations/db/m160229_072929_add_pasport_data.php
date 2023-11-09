<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160229_072929_add_pasport_data extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%passport_data}}', [
            'id' => $this->primaryKey(),
            'questionary_id' => $this->integer()->notNull(),
            'issued_by' => $this->string("1000")->notNull(),
            'issued_date' => $this->string('100')->notNull(),
            'citizenship_id' => $this->integer()->notNull(),
            'document_type_id' => $this->integer()->notNull(),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
        $this->addForeignKey('fk_passport_data_questionary', '{{%passport_data}}', 'questionary_id', '{{%abiturient_questionary}}', 'id', 'cascade', 'cascade');
        $this->addForeignKey('fk_passport_data_doctype', '{{%passport_data}}', 'document_type_id', '{{%dictionary_document_type}}', 'id', 'cascade', 'cascade');
        $this->addForeignKey('fk_passport_data_citizenship', '{{%passport_data}}', 'citizenship_id', '{{%dictionary_citizenship}}', 'id', 'cascade', 'cascade');
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_passport_data_questionary', '{{%passport_data}}');
        $this->dropForeignKey('fk_passport_data_doctype', '{{%passport_data}}');
        $this->dropForeignKey('fk_passport_data_citizenship', '{{%passport_data}}');
        
        $this->dropTable('{{%passport_data}}'); 

        Yii::$app->db->schema->refresh();
    }
}
