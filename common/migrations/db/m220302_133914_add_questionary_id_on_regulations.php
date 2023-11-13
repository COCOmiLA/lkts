<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220302_133914_add_questionary_id_on_regulations extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%user_regulation}}', 'abiturient_questionary_id', $this->integer());
        $this->createIndex('idx-regulation-questionary', '{{%user_regulation}}', 'abiturient_questionary_id');
        $this->addForeignKey('fk_regulations_questionary', '{{%user_regulation}}', 'abiturient_questionary_id', '{{%abiturient_questionary}}', 'id', 'restrict', 'restrict');


    }

    


    public function safeDown()
    {
        $this->dropForeignKey('fk_regulations_questionary', '{{%user_regulation}}');
        $this->dropIndex('idx-regulation-questionary', '{{%user_regulation}}');
        $this->dropColumn('{{%user_regulation}}', 'abiturient_questionary_id');
    }
}
