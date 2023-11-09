<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m230516_080106_create_condition_type_reference_type_table extends MigrationWithDefaultOptions
{
    private const TN = '{{%condition_type_reference_type}}';

    


    public function safeUp()
    {
        if (Yii::$app->db->schema->getTableSchema(self::TN) === null) {
            $this->createReferenceTable(self::TN);
        }
    }

    


    public function safeDown()
    {
        if (Yii::$app->db->schema->getTableSchema(self::TN) !== null) {
            $this->dropReferenceTable(self::TN);
        }
    }
}
