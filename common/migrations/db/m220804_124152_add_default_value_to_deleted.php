<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220804_124152_add_default_value_to_deleted extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->alterColumn('{{%attachment}}', 'deleted', $this->boolean()->defaultValue(false));

        $this->alterColumn('{{%admission_agreement_to_delete}}', 'archive', $this->boolean()->defaultValue(false));
        $this->alterColumn('{{%agreement_decline}}', 'archive', $this->boolean()->defaultValue(false));
        $this->alterColumn('{{%bachelor_preferences}}', 'archive', $this->boolean()->defaultValue(false));
        $this->alterColumn('{{%bachelor_target_reception}}', 'archive', $this->boolean()->defaultValue(false));
    }

    


    public function safeDown()
    {
        $this->alterColumn('{{%attachment}}', 'deleted', $this->boolean()->defaultValue(null));

        $this->alterColumn('{{%admission_agreement_to_delete}}', 'archive', $this->boolean()->defaultValue(null));
        $this->alterColumn('{{%agreement_decline}}', 'archive', $this->boolean()->defaultValue(null));
        $this->alterColumn('{{%bachelor_preferences}}', 'archive', $this->boolean()->defaultValue(null));
        $this->alterColumn('{{%bachelor_target_reception}}', 'archive', $this->boolean()->defaultValue(null));
    }
}
