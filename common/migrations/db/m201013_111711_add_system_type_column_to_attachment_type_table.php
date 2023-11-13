<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\AttachmentType;




class m201013_111711_add_system_type_column_to_attachment_type_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%attachment_type}}', 'system_type', $this->integer()->null());

        AttachmentType::updateAll([
            'system_type' => AttachmentType::SYSTEM_TYPE_COMMON
        ]);
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%attachment_type}}', 'system_type');
    }
}
