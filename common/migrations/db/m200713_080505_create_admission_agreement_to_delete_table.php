<?php

use common\components\Migration\MigrationWithDefaultOptions;








class m200713_080505_create_admission_agreement_to_delete_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%admission_agreement_to_delete}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'agreement_id' => $this->integer(),
            'application_code' => $this->string(),
            'campaign_code' => $this->string(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ]);

        
        $this->createIndex(
            '{{%idx-admission_agreement_to_delete-user_id}}',
            '{{%admission_agreement_to_delete}}',
            'user_id'
        );

        
        $this->addForeignKey(
            '{{%fk-admission_agreement_to_delete-user_id}}',
            '{{%admission_agreement_to_delete}}',
            'user_id',
            '{{%user}}',
            'id',
            'no action'
        );

        
        $this->createIndex(
            '{{%idx-admission_agreement_to_delete-agreement_id}}',
            '{{%admission_agreement_to_delete}}',
            'agreement_id'
        );

        
        $this->addForeignKey(
            '{{%fk-admission_agreement_to_delete-agreement_id}}',
            '{{%admission_agreement_to_delete}}',
            'agreement_id',
            '{{%admission_agreement}}',
            'id',
            'no action'
        );
        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        
        $this->dropForeignKey(
            '{{%fk-admission_agreement_to_delete-user_id}}',
            '{{%admission_agreement_to_delete}}'
        );

        
        $this->dropIndex(
            '{{%idx-admission_agreement_to_delete-user_id}}',
            '{{%admission_agreement_to_delete}}'
        );

        
        $this->dropForeignKey(
            '{{%fk-admission_agreement_to_delete-agreement_id}}',
            '{{%admission_agreement_to_delete}}'
        );

        
        $this->dropIndex(
            '{{%idx-admission_agreement_to_delete-agreement_id}}',
            '{{%admission_agreement_to_delete}}'
        );

        $this->dropTable('{{%admission_agreement_to_delete}}');
        Yii::$app->db->schema->refresh();
    }
}
