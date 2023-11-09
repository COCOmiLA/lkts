<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210521_055653_alter_periods_to_send_agreement_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->alterColumn('{{%periods_to_send_agreement}}', 'start', $this->integer(11)->notNull());
        $this->alterColumn('{{%periods_to_send_agreement}}', 'end', $this->integer(11)->notNull());
    }

    


    public function safeDown()
    {
        $this->alterColumn('{{%periods_to_send_agreement}}', 'start', $this->integer()->notNull());
        $this->alterColumn('{{%periods_to_send_agreement}}', 'end', $this->integer()->notNull());
    }

}
