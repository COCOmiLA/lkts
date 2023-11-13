<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200613_024034_add_additional_column_to_indevidual_achivements_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%individual_achievement}}', 'additional', $this->string(1000));
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%individual_achievement}}', 'additional');
    }
}
