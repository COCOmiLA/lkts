<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210128_065226_add_ref_keys_to_privileges extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $columns = [
            'privilege_id' => 'dictionary_privileges',
            'special_mark_id' => 'dictionary_special_marks',
            'document_type_id' => 'dictionary_document_type',
        ];


        foreach ($columns as $column => $table) {

            $this->addColumn('{{%bachelor_preferences}}', $column, $this->integer());
            if ($column == 'document_type_id') {
                $this->addColumn('{{%bachelor_target_reception}}', $column, $this->integer());
                $this->createIndex(
                    '{{%idx-bachelor_target_reception-' . $column . '}}',
                    '{{%bachelor_target_reception}}',
                    $column
                );
                $this->addForeignKey(
                    '{{%fk-bachelor_target_reception-' . $column . '}}',
                    '{{%bachelor_target_reception}}',
                    $column,
                    '{{%' . $table . '}}',
                    'id',
                    'NO ACTION'
                );
            }

            $this->createIndex(
                '{{%idx-bachelor_preferences-' . $column . '}}',
                '{{%bachelor_preferences}}',
                $column
            );


            $this->addForeignKey(
                '{{%fk-bachelor_preferences-' . $column . '}}',
                '{{%bachelor_preferences}}',
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
            'privilege_id' => 'dictionary_privileges',
            'special_mark_id' => 'dictionary_special_marks',
            'document_type_id' => 'dictionary_document_type',
        ];

        foreach ($columns as $column => $table) {
            if ($column == 'document_type_id') {
                $this->dropForeignKey(
                    '{{%fk-bachelor_target_reception-' . $column . '}}',
                    '{{%bachelor_target_reception}}'
                );
                $this->dropIndex(
                    '{{%idx-bachelor_target_reception-' . $column . '}}',
                    '{{%bachelor_target_reception}}'
                );
                $this->dropColumn('{{%bachelor_target_reception}}', $column);

            }
            $this->dropForeignKey(
                '{{%fk-bachelor_preferences-' . $column . '}}',
                '{{%bachelor_preferences}}'
            );


            $this->dropIndex(
                '{{%idx-bachelor_preferences-' . $column . '}}',
                '{{%bachelor_preferences}}'
            );

            $this->dropColumn('{{%bachelor_preferences}}', $column);
        }
    }

}
