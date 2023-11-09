<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m201013_153454_set_nullable_file_column_in_target_pref_olymp extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->alterColumn('{{%bachelor_target_reception}}', 'file', $this->string(255)->null()->defaultValue(null));
        $this->alterColumn('{{%bachelor_preferences}}', 'file', $this->string(255)->null()->defaultValue(null));
    }

    


    public function safeDown()
    {
        return;
    }

    













}
