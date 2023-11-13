<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m230126_090155_add_admission_campaign_column extends MigrationWithDefaultOptions
{
    private const TN = '{{%admission_campaign}}';

    


    public function safeUp()
    {
        if (!$this->db->getTableSchema(self::TN)->getColumn('common_education_document')) {
            $this->addColumn(self::TN, 'common_education_document', $this->boolean()->defaultValue(false));
        }
        if (!$this->db->getTableSchema(self::TN)->getColumn('allow_multiply_education_documents')) {
            $this->addColumn(self::TN, 'allow_multiply_education_documents', $this->boolean()->defaultValue(false));
        }
    }

    


    public function safeDown()
    {
        if ($this->db->getTableSchema(self::TN)->getColumn('common_education_document')) {
            $this->dropColumn(self::TN, 'common_education_document');
        }
        if ($this->db->getTableSchema(self::TN)->getColumn('allow_multiply_education_documents')) {
            $this->dropColumn(self::TN, 'allow_multiply_education_documents');
        }
    }
}
