<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m170518_135808_delete_ownage_form_id_from_education_data extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->dropColumn('{{%education_data}}', 'ownage_form_id');
    }

    public function safeDown()
    {
        $this->addColumn('{{%education_data}}', 'ownage_form_id', $this->integer(11));
    }
}
