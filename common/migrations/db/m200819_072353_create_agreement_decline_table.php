<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200819_072353_create_agreement_decline_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%agreement_decline}}', [
            'id' => $this->primaryKey(),
            'agreement_id' => $this->integer(),
            'extension' => $this->string(255),
            'file' => $this->string(1000)->notNull(),
            'filename' => $this->string(255),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ]);

        
        $this->createIndex(
            '{{%idx-agreement_decline-agreement_id}}',
            '{{%agreement_decline}}',
            'agreement_id'
        );

        
        $this->addForeignKey(
            '{{%fk-agreement_decline-agreement_id}}',
            '{{%agreement_decline}}',
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
            '{{%fk-agreement_decline-agreement_id}}',
            '{{%agreement_decline}}'
        );

        
        $this->dropIndex(
            '{{%idx-agreement_decline-agreement_id}}',
            '{{%agreement_decline}}'
        );

        $this->dropTable('{{%agreement_decline}}');
        Yii::$app->db->schema->refresh();
    }
}
