<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210415_091126_add_column_to_admission_agreement extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('admission_agreement', 'sended_at', $this->integer()->defaultValue(null));

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropColumn('admission_agreement', 'sended_at');

        Yii::$app->db->schema->refresh();
    }
}
