<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\AttachmentType;




class m201009_131536_set_all_created_attachment_type_using extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        AttachmentType::updateAll(['is_using' => 1], [
            'is_using' => null,
            'campaign_code' => null
            ]);
        return true;
    }

    


    public function safeDown()
    {
        echo "m201009_131536_set_all_created_attachment_type_using cannot be reverted.\n";

        return ;
    }

    













}
