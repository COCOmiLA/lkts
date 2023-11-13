<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m180614_085752_add_enable_check_ege_in_application_type extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn(
            '{{%application_type}}',
            'enable_check_ege',
            $this->boolean()->defaultValue(false)
        );

        \Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropColumn(
            '{{%application_type}}',
            'enable_check_ege'
        );

        \Yii::$app->db->schema->refresh();
    }
}
