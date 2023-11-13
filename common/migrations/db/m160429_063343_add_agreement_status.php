<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160429_063343_add_agreement_status extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->addColumn('{{%admission_agreement}}', 'status', $this->integer());
        $this->alterColumn('{{%admission_agreement}}','file',$this->string(1000));
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%admission_agreement}}', 'status');
         $this->alterColumn('{{%admission_agreement}}','file',$this->string(1000)->notNull());
        Yii::$app->db->schema->refresh();
    }
}
