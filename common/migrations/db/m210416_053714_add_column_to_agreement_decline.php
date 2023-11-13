<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210416_053714_add_column_to_agreement_decline extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('agreement_decline', 'sended_at', $this->integer()->defaultValue(null));

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropColumn('agreement_decline', 'sended_at');

        Yii::$app->db->schema->refresh();
    }
}
