<?php

use backend\models\DocumentTemplate;
use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Query;




class m221229_115827_delete_doc_template_record extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        try {
            $template_to_delete = DocumentTemplate::findOne(['name' => 'agreement_decline_document']);
            if (!is_null($template_to_delete)) {
                $template_to_delete->deleteAttachedFile();
            }
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
        }

        $this->delete('{{%document_template}}', ['name' => 'agreement_decline_document']);
    }

    


    public function safeDown()
    {
        $exists = (new Query())->from('{{%document_template}}')->andWhere([
            'name' => 'agreement_decline_document'
        ])->exists();

        if (!$exists) {
            $this->insert('{{%document_template}}', [
                'name' => 'agreement_decline_document',
                'description' => 'Пустой бланк отказа от согласия на зачисление'
            ]);
        }
    }
}
