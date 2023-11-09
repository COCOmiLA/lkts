<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200613_110847_add_contest_allowed_column_to_admission_campaign_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%admission_campaign}}', 'contest_allowed', $this->boolean());
        $this->addColumn('{{%admission_campaign}}', 'multiply_applications_allowed', $this->boolean());
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%admission_campaign}}', 'contest_allowed');
        $this->dropColumn('{{%admission_campaign}}', 'multiply_applications_allowed');
    }
}
