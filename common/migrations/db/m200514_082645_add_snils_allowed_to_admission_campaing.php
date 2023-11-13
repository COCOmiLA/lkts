<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200514_082645_add_snils_allowed_to_admission_campaing extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('admission_campaign', 'snils_allowed', $this->tinyInteger(1));
    }

    


    public function safeDown()
    {
        $this->dropColumn('admission_campaign', 'snils_allowed');
    }

    













}
