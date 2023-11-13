<?php

use common\components\AttachmentManager;
use common\components\Migration\MigrationWithDefaultOptions;
use common\components\PageRelationManager;
use common\models\AttachmentType;
use common\models\dictionary\DocumentType;




class m220426_010010_add_application_return_attachment_type extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $document_type = DocumentType::findOne([
            'description' => 'Заявление',
            'is_predefined' => true
        ]);
        
        $type = new AttachmentType();
        $type->name = 'Заявление на отзыв документов';
        $type->system_type = AttachmentType::SYSTEM_TYPE_APPLICATION_RETURN;
        $type->required = false;
        $type->related_entity = PageRelationManager::RELATED_ENTITY_APPLICATION;
        $type->from1c = null;
        $type->campaign_code = null;
        $type->document_type_id = $document_type->id ?? null;
        $type->is_using = true;
        $type->hidden = false;

        $type->save(false);
    }

    


    public function safeDown()
    {
        $type = AttachmentManager::GetSystemAttachmentType(AttachmentType::SYSTEM_TYPE_APPLICATION_RETURN);
        if (!is_null($type)) {
            $type->delete();
        }
    }
}
