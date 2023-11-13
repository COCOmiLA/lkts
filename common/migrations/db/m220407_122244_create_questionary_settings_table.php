<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220407_122244_create_questionary_settings_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%questionary_settings}}', [
            'id' => $this->primaryKey(),

            'name' => $this->string()->notNull(),
            'value' => $this->string()->notNull(),
            'description' => $this->string(),

            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('{{%idx-questionary_settings-name}}', '{{%questionary_settings}}', 'name');

        $this->insert('{{%questionary_settings}}', [
            'name' => 'allow_edit_questionary_after_approve',
            'value' => 0,
            'description' => 'Разрешить редактирование анкеты после одобрения.',

            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->insert('{{%questionary_settings}}', [
            'name' => 'allow_edit_address_after_approve',
            'value' => 0,
            'description' => 'Разрешить редактирование адреса после одобрения.',

            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->insert('{{%questionary_settings}}', [
            'name' => 'allow_add_new_passport_after_approve',
            'value' => 0,
            'description' => 'Разрешить добавлять новый паспорт после одобрения.',

            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $this->insert('{{%questionary_settings}}', [
            'name' => 'allow_add_new_file_to_passport_after_approve',
            'value' => 0,
            'description' => 'Разрешить добавлять новый файл к паспорту после одобрения.',

            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $this->insert('{{%questionary_settings}}', [
            'name' => 'allow_delete_file_from_passport_after_approve',
            'value' => 0,
            'description' => 'Разрешить удалять файл из паспорта после одобрения.',

            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->insert('{{%questionary_settings}}', [
            'name' => 'can_change_passport_after_first_application',
            'value' => 0,
            'description' => 'Сможет ли поступающий после первой подачи заявления менять паспортные данные.',

            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $this->insert('{{%questionary_settings}}', [
            'name' => 'can_change_fio_after_first_application',
            'value' => 0,
            'description' => 'Сможет ли поступающий после первой подачи заявления менять ФИО.',

            'created_at' => time(),
            'updated_at' => time(),
        ]);

        Yii::$app->db->schema->refresh();

        $this->delete(
            '{{%code_settings}}',
            ['name' => [
                'block_questionary_after_approve',
                'can_change_fio_after_first_application',
            ]]
        );
    }

    


    public function safeDown()
    {
        $this->dropTable('{{%questionary_settings}}');

        Yii::$app->db->schema->refresh();

        $this->insert('{{%code_settings}}', [
            'name' => 'block_questionary_after_approve',
            'value' => 0,
            'description' => 'Блокировать редактирование анкеты после одобрения.',
        ]);
        $this->insert('{{%code_settings}}', [
            'name' => 'can_change_fio_after_first_application',
            'value' => 1,
            'description' => 'Сможет ли поступающий после первой подачи заявления менять ФИО и паспортные данные.',
        ]);
    }
}
