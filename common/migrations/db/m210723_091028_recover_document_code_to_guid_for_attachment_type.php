<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\AttachmentType;
use common\models\dictionary\DocumentType;




class m210723_091028_recover_document_code_to_guid_for_attachment_type extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $attachmentTypes = AttachmentType::find()
            ->where(['not', [ 'document_type' => null]])
            ->andWhere(['document_type_guid' => null])
            ->andWhere(['document_type_id' => null])->all();
        foreach ($attachmentTypes as $attachmentType) {
            $recoveredDocumentTypeQ = DocumentType::find()->where(['code' => $attachmentType->document_type, 'archive' => false]);
            if((int)$recoveredDocumentTypeQ->count() === 1) {
                $recoveredDocumentType = $recoveredDocumentTypeQ->one();
                if(!is_null($recoveredDocumentType)) {
                    $attachmentType->document_type_id = $recoveredDocumentType->id;
                    $attachmentType->document_type_guid = $recoveredDocumentType->ref_key;
                    if($attachmentType->validate(['document_type_guid', 'document_type_id'])) {
                        $attachmentType->save(false, ['document_type_guid', 'document_type_id']);
                    }
                }
            }
         }

        $attachmentTypesSimplified = AttachmentType::find()
            ->where(['not', [ 'document_type_id' => null]])
            ->andWhere(['document_type_guid' => null])->all();
        foreach ($attachmentTypesSimplified as $attachmentType) {
            $recoveredDocumentType = $attachmentType->documentType;
            if(!is_null($recoveredDocumentType)) {
                $attachmentType->document_type_id = $recoveredDocumentType->id;
                $attachmentType->document_type_guid = $recoveredDocumentType->ref_key;
                if($attachmentType->validate(['document_type_guid', 'document_type_id'])) {
                    $attachmentType->save(false, ['document_type_guid', 'document_type_id']);
                }
            }
        }
    }

    


    public function safeDown()
    {

        return true;
    }

    













}
