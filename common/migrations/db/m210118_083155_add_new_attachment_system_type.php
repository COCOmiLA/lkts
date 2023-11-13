<?php

use common\components\AttachmentManager;
use common\components\Migration\MigrationWithDefaultOptions;
use common\components\PageRelationManager;
use common\models\AttachmentType;




class m210118_083155_add_new_attachment_system_type extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        Yii::$app->configurationManager->suspendUnspecifiedCodesError(true);

        $type = new AttachmentType();
        $type->name = 'Договоры об оказании платных образовательных услуг';
        $type->system_type = AttachmentType::SYSTEM_TYPE_FULL_RECOVERY_SPECIALITY;
        $type->required = false;
        $type->related_entity = PageRelationManager::RELATED_ENTITY_APPLICATION;
        $type->from1c = null;
        $type->campaign_code = null;
        $type->document_type = Yii::$app->configurationManager->getCode('paid_contract_document_type');
        $type->is_using = true;
        $type->hidden = false;

        $type->save(false);
        Yii::$app->configurationManager->suspendUnspecifiedCodesError(false);
    }

    


    public function safeDown()
    {
        $type = AttachmentManager::GetSystemAttachmentType(AttachmentType::SYSTEM_TYPE_FULL_RECOVERY_SPECIALITY);
        if (!is_null($type)) {
            $type->delete();
        }
    }

}
