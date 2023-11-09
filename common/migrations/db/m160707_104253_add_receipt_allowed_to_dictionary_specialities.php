<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m160707_104253_add_receipt_allowed_to_dictionary_specialities extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%dictionary_speciality}}', 'receipt_allowed',$this->smallInteger()->defaultValue(1));
        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%dictionary_speciality}}', 'receipt_allowed');
        Yii::$app->db->schema->refresh();
    }
}
