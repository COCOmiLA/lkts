<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210726_093116_add_index_to_attachment_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createIndex(
            '{{%idx-attachment-application_id}}',
            '{{%attachment}}',
            'application_id'
        );

        $this->createIndex(
            '{{%idx-attachment-questionary_id}}',
            '{{%attachment}}',
            'questionary_id'
        );
    }

    


    public function safeDown()
    {
        $this->dropIndex('idx-attachment-application_id', '{{%attachment}}');
        $this->dropIndex('idx-attachment-questionary_id', '{{%attachment}}');
    }
}
