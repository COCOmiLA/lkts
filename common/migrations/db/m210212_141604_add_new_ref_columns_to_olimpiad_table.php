<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210212_141604_add_new_ref_columns_to_olimpiad_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $columns = [
            'ref_id' => 'olympic_reference_type',
            'olympic_type_ref_id' => 'olympic_type_reference_type',
            'olympic_level_ref_id' => 'olympic_level_reference_type',
            'olympic_kind_ref_id' => 'olympic_kind_reference_type',
            'olympic_class_ref_id' => 'olympic_class_reference_type',
            'olympic_profile_ref_id' => 'olympic_profile_reference_type',
        ];


        foreach ($columns as $column => $table) {

            $this->addColumn('{{%dictionary_olympiads}}', $column, $this->integer());

            $this->createIndex(
                '{{%idx-olympiads-' . $column . '}}',
                '{{%dictionary_olympiads}}',
                $column
            );


            $this->addForeignKey(
                '{{%fk-dictionary_olympiads-' . $column . '}}',
                '{{%dictionary_olympiads}}',
                $column,
                '{{%' . $table . '}}',
                'id',
                'NO ACTION'
            );
        }


        $this->addColumn('{{%bachelor_preferences}}', 'olympiad_id', $this->integer());

        $this->createIndex(
            '{{%idx-bachelor_preferences-olympiad_id}}',
            '{{%bachelor_preferences}}',
            'olympiad_id'
        );


        $this->addForeignKey(
            '{{%fk-bachelor_preferences-olympiad_id}}',
            '{{%bachelor_preferences}}',
            'olympiad_id',
            '{{%dictionary_olympiads}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {


        $this->dropForeignKey(
            '{{%fk-bachelor_preferences-olympiad_id}}',
            '{{%bachelor_preferences}}'
        );


        $this->dropIndex(
            '{{%idx-bachelor_preferences-olympiad_id}}',
            '{{%bachelor_preferences}}'
        );

        $this->dropColumn('{{%bachelor_preferences}}', 'olympiad_id');

        $columns = [
            'ref_id' => 'olympic_reference_type',
            'olympic_type_ref_id' => 'olympic_type_reference_type',
            'olympic_level_ref_id' => 'olympic_level_reference_type',
            'olympic_kind_ref_id' => 'olympic_kind_reference_type',
            'olympic_class_ref_id' => 'olympic_class_reference_type',
            'olympic_profile_ref_id' => 'olympic_profile_reference_type',
        ];

        foreach ($columns as $column => $table) {

            $this->dropForeignKey(
                '{{%fk-dictionary_olympiads-' . $column . '}}',
                '{{%dictionary_olympiads}}'
            );


            $this->dropIndex(
                '{{%idx-olympiads-' . $column . '}}',
                '{{%dictionary_olympiads}}'
            );

            $this->dropColumn('{{%dictionary_olympiads}}', $column);
        }
    }
}
