<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m190422_125256_add_column_to__individual_achievement extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {

    }

    


    public function safeDown()
    {
        echo "m190422_125256_add_column_to__individual_achievement cannot be reverted.\n";

        return false;
    }

    public function up()
    {
        $this->addColumn('{{%individual_achievement}}', 'document_type', $this->string(255), 'type AFTER document_giver');
        Yii::$app->db->schema->refresh();

    }

    public function down()
    {
        $this->dropColumn('{{%individual_achievement}}', 'document_type');
        Yii::$app->db->schema->refresh();
    }
}
