<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\components\ReferenceTypeManager\traits\migrations\createDropReferenceTable;




class m230118_054558_create_cget_required_preference_table extends MigrationWithDefaultOptions
{
    use createDropReferenceTable;

    private const TN = '{{%cget_required_preference}}';

    


    public function safeUp()
    {
        $this->createTable(self::TN, [
            'id' => $this->primaryKey(),
            'cget_entrance_test_set_id' => $this->integer()->defaultValue(null),

            'dictionary_privileges_id' => $this->integer()->defaultValue(null),
            'dictionary_special_marks_id' => $this->integer()->defaultValue(null),

            'updated_at' => $this->integer()->defaultValue(null),
            'created_at' => $this->integer()->defaultValue(null),

            'archive' => $this->boolean()->defaultValue(false),
        ]);

        $normalTablename = self::normalizeTablename(self::TN);

        $this->createIndex(
            "IDX-{$normalTablename}-to-cget_ets",
            self::TN,
            'cget_entrance_test_set_id'
        );
        $this->addForeignKey(
            "FK-{$normalTablename}-to-cget_ets",
            self::TN,
            'cget_entrance_test_set_id',
            '{{%cget_entrance_test_set}}',
            'id',
            'NO ACTION'
        );

        $this->createIndex(
            "IDX-{$normalTablename}-to-dp",
            self::TN,
            'dictionary_privileges_id'
        );
        $this->addForeignKey(
            "FK-{$normalTablename}-to-dp",
            self::TN,
            'dictionary_privileges_id',
            '{{%dictionary_privileges}}',
            'id',
            'NO ACTION'
        );

        $this->createIndex(
            "IDX-{$normalTablename}-to-dsm",
            self::TN,
            'dictionary_special_marks_id'
        );
        $this->addForeignKey(
            "FK-{$normalTablename}-to-dsm",
            self::TN,
            'dictionary_special_marks_id',
            '{{%dictionary_special_marks}}',
            'id',
            'NO ACTION'
        );

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $normalTablename = self::normalizeTablename(self::TN);

        $this->dropForeignKey("FK-{$normalTablename}-to-dp",       self::TN);
        $this->dropForeignKey("FK-{$normalTablename}-to-dsm",      self::TN);
        $this->dropForeignKey("FK-{$normalTablename}-to-cget_ets", self::TN);

        $this->dropIndex("IDX-{$normalTablename}-to-dp",       self::TN);
        $this->dropIndex("IDX-{$normalTablename}-to-dsm",      self::TN);
        $this->dropIndex("IDX-{$normalTablename}-to-cget_ets", self::TN);

        $this->dropTable(self::TN);

        Yii::$app->db->schema->refresh();
    }
}
