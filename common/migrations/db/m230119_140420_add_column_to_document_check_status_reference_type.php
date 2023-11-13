<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m230119_140420_add_column_to_document_check_status_reference_type extends MigrationWithDefaultOptions
{
    private const TN = '{{%document_check_status_reference_type}}';

    


    public function safeUp()
    {
        $this->addColumn(self::TN, 'human_readable_name', $this->string()->defaultValue(null));
    }

    


    public function safeDown()
    {
        $this->dropColumn(self::TN, 'human_readable_name');
    }
}
