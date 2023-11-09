<?php

use common\components\AttachmentManager;
use common\components\Migration\MigrationWithDefaultOptions;
use common\components\PageRelationManager;
use common\models\AttachmentType;




class m201014_111532_add_preference_system_attechment_types extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $type = new AttachmentType();
        $type->name = 'Документы льгот и преимущественных прав';
        $type->system_type = AttachmentType::SYSTEM_TYPE_PREFERENCE;
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
        $type = AttachmentManager::GetSystemAttachmentType(AttachmentType::SYSTEM_TYPE_PREFERENCE);

        if ($type !== null) {
            $type->delete();
        }
    }


}
