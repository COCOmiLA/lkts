<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m230214_064431_add_column_admission_campaign extends MigrationWithDefaultOptions
{
    private const TN = '{{%admission_campaign}}';
    private const ADDITIONAL_COLUMN = 'separate_statement_for_full_payment_budget';

    


    public function safeUp()
    {
        if (!$this->db->getTableSchema(self::TN)->getColumn(self::ADDITIONAL_COLUMN)) {
            $this->addColumn(self::TN, self::ADDITIONAL_COLUMN, $this->boolean()->defaultValue(false));
        }
    }

    


    public function safeDown()
    {
        if ($this->db->getTableSchema(self::TN)->getColumn(self::ADDITIONAL_COLUMN)) {
            $this->dropColumn(self::TN, self::ADDITIONAL_COLUMN);
        }
    }
}
