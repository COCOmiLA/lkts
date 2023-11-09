<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\components\ReferenceTypeManager\traits\migrations\createDropReferenceTable;




class m220114_090853_create_bachelor_result_centralized_testing_table extends MigrationWithDefaultOptions
{
    use createDropReferenceTable;

    private const FK_LIST = [
        'egeresult_id' => 'bachelor_egeresult',
        'passed_subject_ref_id' => 'discipline_reference_type',
    ];

    


    public function safeUp()
    {
        Yii::$app->db->schema->refresh();

        $this->createTable('{{%bachelor_result_centralized_testing}}', [
            'id' => $this->primaryKey(),

            'egeresult_id' => $this->integer()->defaultValue(null),

            'series' => $this->string()->defaultValue(null),
            'number' => $this->string()->defaultValue(null),
            'year' => $this->string(4)->defaultValue(null),
            'passed_subject_ref_id' => $this->integer()->defaultValue(null),
            'mark' => $this->integer()->defaultValue(null),

            'updated_at' => $this->integer()->defaultValue(null),
            'created_at' => $this->integer()->defaultValue(null),

            'archive' => $this->boolean()->defaultValue(false),
            'archived_at' => $this->integer()->defaultValue(null),
        ]);

        Yii::$app->db->schema->refresh();

        foreach (self::FK_LIST as $columns => $refTable) {
            $this->addForeignKey(
                "FK_to_{$refTable}_from_BRCT",
                '{{%bachelor_result_centralized_testing}}',
                $columns,
                "{{%{$refTable}}}",
                'id'
            );
        }

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        Yii::$app->db->schema->refresh();

        foreach (self::FK_LIST as $refTable) {
            $this->dropForeignKey("FK_to_{$refTable}_from_BRCT", '{{%bachelor_result_centralized_testing}}');
        }

        $this->dropTable('{{%bachelor_result_centralized_testing}}');

        Yii::$app->db->schema->refresh();
    }
}
