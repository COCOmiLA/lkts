<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160418_140227_change_row_order_type extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $new_type = $this->integer();
        if ($this->db->driverName === 'pgsql') {
            $new_type = $new_type->append('USING CAST(row_number AS integer)');
        }
        $this->alterColumn('{{%competition_list_rows}}', 'row_number', $new_type);
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->alterColumn('{{%competition_list_rows}}', 'row_number', $this->string(1000));
        Yii::$app->db->schema->refresh();
    }
}
