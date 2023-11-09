<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m160623_075402_add_category_to_text_settigns extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%text_settings}}', 'category', $this->integer()->notNull()->defaultValue(0));
        Yii::$app->db->schema->refresh();
        
        $this->update('{{%text_settings}}', ['category' => 1], ['id' => 1]);
        $this->update('{{%text_settings}}', ['category' => 1], ['id' => 2]);
        $this->update('{{%text_settings}}', ['category' => 1], ['id' => 3]);
        $this->update('{{%text_settings}}', ['category' => 1], ['id' => 4]);
        $this->update('{{%text_settings}}', ['category' => 2], ['id' => 5]);
        $this->update('{{%text_settings}}', ['category' => 2], ['id' => 6]);
        $this->update('{{%text_settings}}', ['category' => 2], ['id' => 7]);
        $this->update('{{%text_settings}}', ['category' => 2], ['id' => 8]);
        $this->update('{{%text_settings}}', ['category' => 2], ['id' => 9]);
        $this->update('{{%text_settings}}', ['category' => 2], ['id' => 10]);
        $this->update('{{%text_settings}}', ['category' => 2], ['id' => 11]);
        $this->update('{{%text_settings}}', ['category' => 0], ['id' => 12]);
        $this->update('{{%text_settings}}', ['category' => 3], ['id' => 13]);
        $this->update('{{%text_settings}}', ['category' => 3], ['id' => 14]);
        $this->update('{{%text_settings}}', ['category' => 3], ['id' => 15]);
        $this->update('{{%text_settings}}', ['category' => 3], ['id' => 16]);
        $this->update('{{%text_settings}}', ['category' => 3], ['id' => 17]);
        $this->update('{{%text_settings}}', ['category' => 3], ['id' => 18]);
        $this->update('{{%text_settings}}', ['category' => 0], ['id' => 19]);
        $this->update('{{%text_settings}}', ['category' => 0], ['id' => 20]);
        $this->update('{{%text_settings}}', ['category' => 0], ['id' => 21]);
        $this->update('{{%text_settings}}', ['category' => 4], ['id' => 22]);
        $this->update('{{%text_settings}}', ['category' => 4], ['id' => 23]);
        $this->update('{{%text_settings}}', ['category' => 4], ['id' => 24]);
        $this->update('{{%text_settings}}', ['category' => 4], ['id' => 25]);
        $this->update('{{%text_settings}}', ['category' => 4], ['id' => 26]);
        $this->update('{{%text_settings}}', ['category' => 4], ['id' => 27]);
        $this->update('{{%text_settings}}', ['category' => 7], ['id' => 28]);
        $this->update('{{%text_settings}}', ['category' => 7], ['id' => 29]);
        $this->update('{{%text_settings}}', ['category' => 0], ['id' => 30]);
        $this->update('{{%text_settings}}', ['category' => 4], ['id' => 31]);
        $this->update('{{%text_settings}}', ['category' => 4], ['id' => 32]);
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%text_settings}}', 'category');
        Yii::$app->db->schema->refresh();
    }
}
