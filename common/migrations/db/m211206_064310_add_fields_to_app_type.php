<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m211206_064310_add_fields_to_app_type extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%application_type}}', 'allow_special_requirement_selection', $this->boolean()->defaultValue(true));
        $this->addColumn('{{%application_type}}', 'allow_language_selection', $this->boolean()->defaultValue(true));
        Yii::$app->db->schema->refresh();

    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%application_type}}', 'allow_special_requirement_selection');
        $this->dropColumn('{{%application_type}}', 'allow_language_selection');
        Yii::$app->db->schema->refresh();

    }
}
