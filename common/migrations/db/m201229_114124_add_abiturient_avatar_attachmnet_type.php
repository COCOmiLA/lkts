<?php

use common\components\AttachmentManager;
use common\components\Migration\MigrationWithDefaultOptions;
use common\components\PageRelationManager;
use common\models\AttachmentType;




class m201229_114124_add_abiturient_avatar_attachmnet_type extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $type = new AttachmentType();
        $type->name = 'Фото поступающего';
        $type->system_type = AttachmentType::SYSTEM_TYPE_ABITURIENT_AVATAR;
        $type->required = true;
        
        $type->related_entity = PageRelationManager::RELATED_ENTITY_QUESTIONARY;
        $type->from1c = null;
        $type->campaign_code = null;
        
        $type->document_type = null;
        $type->is_using = true;
        $type->hidden = false;

        $type->save(false);
    }

    


    public function safeDown()
    {
        $type = AttachmentManager::GetSystemAttachmentType(AttachmentType::SYSTEM_TYPE_ABITURIENT_AVATAR);

        if ($type !== null) {
            $type->delete();
        }
    }

    













}
