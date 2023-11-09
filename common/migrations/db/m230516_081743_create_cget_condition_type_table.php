<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m230516_081743_create_cget_condition_type_table extends MigrationWithDefaultOptions
{
    private const TN = '{{%cget_condition_type}}';

    


    public function safeUp()
    {
        $this->createTable(self::TN, [
            'id' => $this->primaryKey(),
            'cget_entrance_test_set_id' => $this->integer()->notNull(),
            'condition_type_reference_type_id' => $this->integer()->defaultValue(null),

            'dictionary_education_type_id' => $this->integer()->defaultValue(null),
            'profile_reference_type_id' => $this->integer()->defaultValue(null),
            'privilege_id' => $this->integer()->defaultValue(null),
            'special_mark_id' => $this->integer()->defaultValue(null),

            'updated_at' => $this->integer()->defaultValue(null),
            'created_at' => $this->integer()->defaultValue(null),

            'archive' => $this->boolean()->defaultValue(false),
        ]);


        $normalName = self::normalizeTablename(self::TN);
        $this->createIndex(
            "IDX-{$normalName}_entrance_test_set",
            self::TN,
            'cget_entrance_test_set_id'
        );
        $this->addForeignKey(
            "FK-{$normalName}_entrance_test_set",
            self::TN,
            'cget_entrance_test_set_id',
            '{{%cget_entrance_test_set}}',
            'id',
            'NO ACTION'
        );

        $this->createIndex(
            "IDX-{$normalName}_condition_type_ref",
            self::TN,
            'condition_type_reference_type_id'
        );
        $this->addForeignKey(
            "FK-{$normalName}_condition_type_ref",
            self::TN,
            'condition_type_reference_type_id',
            '{{%condition_type_reference_type}}',
            'id',
            'NO ACTION'
        );


        $this->createIndex(
            "IDX-{$normalName}_profile_ref",
            self::TN,
            'profile_reference_type_id'
        );
        $this->addForeignKey(
            "FK-{$normalName}_profile_ref",
            self::TN,
            'profile_reference_type_id',
            '{{%profile_reference_type}}',
            'id',
            'NO ACTION'
        );
        $this->createIndex(
            "IDX-{$normalName}_dic_education_type",
            self::TN,
            'dictionary_education_type_id'
        );
        $this->addForeignKey(
            "FK-{$normalName}_dic_education_type",
            self::TN,
            'dictionary_education_type_id',
            '{{%dictionary_education_type}}',
            'id',
            'NO ACTION'
        );
        $this->createIndex(
            "IDX-{$normalName}_privilege",
            self::TN,
            'privilege_id'
        );
        $this->addForeignKey(
            "FK-{$normalName}_privilege",
            self::TN,
            'privilege_id',
            '{{%dictionary_privileges}}',
            'id',
            'NO ACTION'
        );
        $this->createIndex(
            "IDX-{$normalName}_special_mark",
            self::TN,
            'special_mark_id'
        );
        $this->addForeignKey(
            "FK-{$normalName}_special_mark",
            self::TN,
            'special_mark_id',
            '{{%dictionary_special_marks}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {
        $normalName = self::normalizeTablename(self::TN);
        $this->dropForeignKey("FK-{$normalName}_privilege",          self::TN);
        $this->dropForeignKey("FK-{$normalName}_special_mark",       self::TN);
        $this->dropForeignKey("FK-{$normalName}_profile_ref",        self::TN);
        $this->dropForeignKey("FK-{$normalName}_entrance_test_set",  self::TN);
        $this->dropForeignKey("FK-{$normalName}_dic_education_type", self::TN);
        $this->dropForeignKey("FK-{$normalName}_condition_type_ref", self::TN);

        $this->dropIndex("IDX-{$normalName}_privilege",          self::TN);
        $this->dropIndex("IDX-{$normalName}_special_mark",       self::TN);
        $this->dropIndex("IDX-{$normalName}_profile_ref",        self::TN);
        $this->dropIndex("IDX-{$normalName}_entrance_test_set",  self::TN);
        $this->dropIndex("IDX-{$normalName}_dic_education_type", self::TN);
        $this->dropIndex("IDX-{$normalName}_condition_type_ref", self::TN);

        $this->dropTable(self::TN);
    }
}
