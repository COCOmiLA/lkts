<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200622_073852_create_attachment_archive_table extends MigrationWithDefaultOptions
{

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%attachment_archive}}', [
            'id' => $this->primaryKey(),
            'attachment_id' => $this->integer(),
            'questionary_id' => $this->integer(),
            'application_id' => $this->integer(),
            'file' => $this->string(1000)->notNull(),
            'filename' => $this->string(1000)->notNull(),
            'extension' => $this->string(),
            'attachment_type_id' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);

        Yii::$app->db->schema->refresh();

    }

    public function down()
    {
        $this->dropTable('{{%attachment_archive}}');

        Yii::$app->db->schema->refresh();
    }
}
