<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200518_104922_create_table_dictionary_olympiads extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        if ($this->db->getTableSchema('{{%dictionary_olympiads}}', true) !== null) {
            $this->dropTable('{{%dictionary_olympiads}}');
        }
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%dictionary_olympiads}}',
            [
                'id' => $this->primaryKey(),
                'code' => $this->string(),
                'year' => $this->string(),
                'type' => $this->string(),
                'place' => $this->string(),
                'kind' => $this->string(),
                'level' => $this->string(),
                'name' => $this->string(),
                'class' => $this->integer(),
                'profile' => $this->string(),
                'updated_at' => $this->integer(),
                'created_at' => $this->integer(),
                'description' => $this->string(),
                'archive' => $this->boolean()->notNull()->defaultValue(false),
                'need_ege' => $this->boolean()->notNull()->defaultValue(false)
            ],
            $tableOptions
        );
        Yii::$app->db->schema->refresh();

        $this->createTable(
            '{{%dictionary_olympiads_filter}}',
            [
                'id' => $this->primaryKey(),
                'id_pk' => $this->string(),
                'updated_at' => $this->integer(),
                'created_at' => $this->integer(),
                'olympiad_code' => $this->string(),
                'specific_mark_code' => $this->string(),
                'archive' => $this->boolean()->notNull()->defaultValue(false)
            ],
            $tableOptions
        );
        Yii::$app->db->schema->refresh();
    }

    


    public function down()
    {
        $this->dropTable('{{%dictionary_olympiads}}');
        $this->dropTable('{{%dictionary_olympiads_filter}}');
        Yii::$app->db->schema->refresh();
    }

    













}
