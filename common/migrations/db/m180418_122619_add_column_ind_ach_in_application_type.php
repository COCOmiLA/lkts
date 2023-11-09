<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m180418_122619_add_column_ind_ach_in_application_type extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn(
            '{{%application_type}}',
            'hide_ind_ach',
            $this->boolean()->defaultValue(false)
        );

        \Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn(
            '{{%application_type}}',
            'hide_ind_ach'
        );

        \Yii::$app->db->schema->refresh();
    }
}
