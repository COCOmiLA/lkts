<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160517_062648_add_link_settings extends MigrationWithDefaultOptions
{
public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%link_settings}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull(),
            'description' => $this->string(1000)->notNull(),
            'title' => $this->string(1000)->notNull(),
            'url' => $this->string(1000),
        ], $tableOptions);
              
        $this->insert('{{%link_settings}}', [
            'name' => 'link_admission_news',
            'description' => 'Ссылка на новости о ходе приема',
            'title' => 'Новости о ходе приёма',
            'url' => 'http://',
        ]);
        
        $this->insert('{{%link_settings}}', [
            'name' => 'link_admission_rules',
            'description' => 'Ссылка на правила приема',
            'title' => 'Правила приёма',
            'url' => 'http://',
        ]);
                
        $this->insert('{{%link_settings}}', [
            'name' => 'link_admission_plan',
            'description' => 'Ссылка на план набора',
            'title' => 'План набора',
            'url' => 'http://',
        ]);
                        
        $this->insert('{{%link_settings}}', [
            'name' => 'link_admission_exam',
            'description' => 'Ссылка на вступительные испытания',
            'title' => 'Вступительные испытания',
            'url' => 'http://',
        ]);
        
        Yii::$app->db->schema->refresh();       
    }

    public function safeDown()
    {
        $this->dropTable('{{%link_settings}}');
        Yii::$app->db->schema->refresh();
    }
}
