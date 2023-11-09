<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\AttachmentType;




class m220425_095855_archive_redundant_attachment_types extends MigrationWithDefaultOptions
{
    private $attachment_type_names = [
        'Разворот паспорта с персональными данными',
        'Разворот паспорта с отметками о регистрации',
        'Документ об образовании',
    ];

    


    public function safeUp()
    {
        $attachment_types = AttachmentType::find()->where(['name' => $this->attachment_type_names])->all();
        foreach ($attachment_types as $attachment_type) {
            $attachment_type->is_using = false;
            $attachment_type->hidden = true;
            $attachment_type->save(false);
        }
    }

    


    public function safeDown()
    {
        $attachment_types = AttachmentType::find()->where(['name' => $this->attachment_type_names])->all();
        foreach ($attachment_types as $attachment_type) {
            $attachment_type->is_using = true;
            $attachment_type->hidden = false;
            $attachment_type->save(false);
        }
    }
}
