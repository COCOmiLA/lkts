<?php

use common\components\AttachmentManager;
use common\components\Migration\MigrationWithDefaultOptions;
use common\components\PageRelationManager;
use common\models\AttachmentType;




class m201013_112302_add_target_reception_system_attachment_type extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $type = new AttachmentType();
        $type->name = 'Документы целевых договоров';
        $type->system_type = AttachmentType::SYSTEM_TYPE_TARGET;
        $type->required = true;
        
        $type->related_entity = PageRelationManager::RELATED_ENTITY_APPLICATION;
        $type->from1c = null;
        $type->campaign_code = null;
        
        $type->document_type = null;
        $type->is_using = true;
        $type->hidden = false;

        $type->save(false);
    }

    


    public function safeDown()
    {
        $type = AttachmentManager::GetSystemAttachmentType(AttachmentType::SYSTEM_TYPE_TARGET);

        if($type !== null) {
            $type->delete();
        }
    }
}
