<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m211119_124034_create_bachelor_entrance_test_set_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable(
            '{{%bachelor_entrance_test_set}}',
            [
                'id' => $this->primaryKey(),
                'priority' => $this->integer()->defaultValue(null),
                'bachelor_egeresult_id' => $this->integer()->defaultValue(null),
                'bachelor_speciality_id' => $this->integer()->defaultValue(null),

                'updated_at' => $this->integer()->defaultValue(null),
                'created_at' => $this->integer()->defaultValue(null),

                'archive' => $this->boolean()->defaultValue(false),
                'archived_at' => $this->integer()->defaultValue(null),
            ]
        );

        Yii::$app->db->schema->refresh();

        $this->addForeignKey(
            'FK_from_bachelor_entrance_test_set_to_bachelor_egeresult',
            '{{%bachelor_entrance_test_set}}',
            'bachelor_egeresult_id',
            'bachelor_egeresult',
            'id'
        );
        $this->addForeignKey(
            'FK_from_bachelor_entrance_test_set_to_bachelor_speciality',
            '{{%bachelor_entrance_test_set}}',
            'bachelor_speciality_id',
            'bachelor_speciality',
            'id'
        );

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropForeignKey('FK_from_bachelor_entrance_test_set_to_bachelor_egeresult', '{{%bachelor_entrance_test_set}}');
        $this->dropForeignKey('FK_from_bachelor_entrance_test_set_to_bachelor_speciality', '{{%bachelor_entrance_test_set}}');

        $this->dropTable('{{%bachelor_entrance_test_set}}');

        Yii::$app->db->schema->refresh();
    }
}
