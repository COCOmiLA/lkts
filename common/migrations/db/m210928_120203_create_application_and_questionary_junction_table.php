<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210928_120203_create_application_and_questionary_junction_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%application_and_questionary_junction}}', [
            'id' => $this->primaryKey(),
            'application_id' => $this->integer()->notNull(),
            'questionary_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex(
            '{{%idx-application_and_questionary-application_id}}',
            '{{%application_and_questionary_junction}}',
            'application_id'
        );

        $this->addForeignKey(
            '{{%fk-application_and_questionary-application_id}}',
            '{{%application_and_questionary_junction}}',
            'application_id',
            '{{%bachelor_application}}',
            'id',
            'CASCADE'
        );

        $this->createIndex(
            '{{%idx-application_and_questionary-questionary_id}}',
            '{{%application_and_questionary_junction}}',
            'questionary_id'
        );

        $this->addForeignKey(
            '{{%fk-application_and_questionary-questionary_id}}',
            '{{%application_and_questionary_junction}}',
            'questionary_id',
            '{{%abiturient_questionary}}',
            'id',
            'CASCADE'
        );

        $this->addColumn('{{%individual_achievement}}', 'application_id', $this->integer());

        $this->createIndex(
            '{{%idx-individual_achievement-application_id}}',
            '{{%individual_achievement}}',
            'application_id'
        );

        $this->addForeignKey(
            '{{%fk-individual_achievement-application_id}}',
            '{{%individual_achievement}}',
            'application_id',
            '{{%bachelor_application}}',
            'id',
            'CASCADE'
        );

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropForeignKey(
            '{{%fk-individual_achievement-application_id}}',
            '{{%individual_achievement}}'
        );

        $this->dropIndex(
            '{{%idx-individual_achievement-application_id}}',
            '{{%individual_achievement}}'
        );
        $this->dropColumn('{{%individual_achievement}}', 'application_id');

        $this->dropForeignKey(
            '{{%fk-application_and_questionary-application_id}}',
            '{{%application_and_questionary_junction}}'
        );

        $this->dropIndex(
            '{{%idx-application_and_questionary-application_id}}',
            '{{%application_and_questionary_junction}}'
        );

        $this->dropForeignKey(
            '{{%fk-application_and_questionary-questionary_id}}',
            '{{%application_and_questionary_junction}}'
        );

        $this->dropIndex(
            '{{%idx-application_and_questionary-questionary_id}}',
            '{{%application_and_questionary_junction}}'
        );

        $this->dropTable('{{%application_and_questionary_junction}}');
    }
}
