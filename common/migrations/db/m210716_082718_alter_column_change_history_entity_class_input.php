<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210716_082718_alter_column_change_history_entity_class_input extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->alterColumn('change_history_entity_class_input',  'value', $this->string(2000));
        $this->alterColumn('change_history_entity_class_input',  'old_value', $this->string(2000));
        $this->alterColumn('change_history_entity_class_input',  'archived_value', $this->string(2000));
        $this->alterColumn('change_history_entity_class_input',  'archived_old_value', $this->string(2000));
    }

    


    public function safeDown()
    {
        echo "m210716_082718_alter_column_change_history_entity_class_input cannot be reverted.\n";

        return false;
    }

    













}
