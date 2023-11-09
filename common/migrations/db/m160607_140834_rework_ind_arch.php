<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160607_140834_rework_ind_arch extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->dropColumn('{{%individual_achievement}}','document_type_id');
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->addColumn('{{%individual_achievement}}','document_type_id',$this->integer()->notNull());
        Yii::$app->db->schema->refresh();
    }
}
