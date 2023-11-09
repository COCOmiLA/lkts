<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m230306_151708_add_column_to_document_check_status_reference_type extends MigrationWithDefaultOptions
{
    private const TN = '{{%document_check_status_reference_type}}';

    


    public function safeUp()
    {
        $this->addColumn(self::TN, 'icon_class', $this->string(50)->defaultValue(null));
        $this->addColumn(self::TN, 'icon_color', $this->string(50)->defaultValue(null));
    }

    


    public function safeDown()
    {
        $this->dropColumn(self::TN, 'icon_class');
        $this->dropColumn(self::TN, 'icon_color');
    }
}
