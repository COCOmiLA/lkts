<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200819_160349_insert_document_agreement_decline_template extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $docuemtTemplate = new \backend\models\DocumentTemplate();
        $docuemtTemplate->name = 'agreement_decline_document';
        $docuemtTemplate->description = 'Пустой бланк отказа от согласия на зачисление';
        if($docuemtTemplate->validate()) {
            $docuemtTemplate->save();
            return true;
        }
        return false;
    }

    


    public function safeDown()
    {
        $docuemtTemplate = \backend\models\DocumentTemplate::findOne([
            'name' => 'agreement_decline_document'
        ]);
        if(isset($docuemtTemplate)) {
            $docuemtTemplate->delete();
        }
    }

    













}
