<?php

use common\components\AttachmentManager;
use common\components\Migration\MigrationWithDefaultOptions;
use common\components\PageRelationManager;
use common\models\AttachmentType;




class m201015_085306_add_individual_achievement_attachment_type extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $type = new AttachmentType();
        $type->name = 'Документы индивидуальных достижений';
        $type->system_type = AttachmentType::SYSTEM_TYPE_INDIVIDUAL_ACHIEVEMENT;
        $type->required = false;
        
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
        $type = AttachmentManager::GetSystemAttachmentType(AttachmentType::SYSTEM_TYPE_INDIVIDUAL_ACHIEVEMENT);
        $type->delete();
    }

    













}
