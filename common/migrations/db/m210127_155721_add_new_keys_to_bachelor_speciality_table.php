<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210127_155721_add_new_keys_to_bachelor_speciality_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $columns = [
            'admission_category_id' => 'dictionary_admission_categories',
        ];

        foreach ($columns as $column => $table) {

            $this->addColumn('{{%bachelor_speciality}}', $column, $this->integer());

            $this->createIndex(
                '{{%idx-bachelor_speciality-' . $column . '}}',
                '{{%bachelor_speciality}}',
                $column
            );

            $this->addForeignKey(
                '{{%fk-bachelor_speciality-' . $column . '}}',
                '{{%bachelor_speciality}}',
                $column,
                '{{%' . $table . '}}',
                'id',
                'NO ACTION'
            );
        }
    }

    


    public function safeDown()
    {
        $columns = [
            'admission_category_id' => 'dictionary_admission_categories',
        ];

        foreach ($columns as $column => $_) {
            $this->dropForeignKey(
                '{{%fk-bachelor_speciality-' . $column . '}}',
                '{{%bachelor_speciality}}'
            );

            $this->dropIndex(
                '{{%idx-bachelor_speciality-' . $column . '}}',
                '{{%bachelor_speciality}}'
            );

            $this->dropColumn('{{%bachelor_speciality}}', $column);
        }
    }

}
