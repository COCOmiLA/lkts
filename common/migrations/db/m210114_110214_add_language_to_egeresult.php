<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210114_110214_add_language_to_egeresult extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        
        $this->dropForeignKey(
            '{{%fk-bachelor_egeresult-language_id}}',
            '{{%bachelor_egeresult}}'
        );

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {

        
        $this->addForeignKey(
            '{{%fk-bachelor_egeresult-language_id}}',
            '{{%bachelor_egeresult}}',
            'language_id',
            '{{%foreign_languages_for_ege}}',
            'id',
            'SET NULL'
        );
        Yii::$app->db->schema->refresh();
    }
}
