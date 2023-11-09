<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m180517_110052_add_fields_in_admission_campaign extends MigrationWithDefaultOptions
{
    


    public function safeUp() {
        $this->addColumn(
            '{{%admission_campaign}}',
            'max_speciality_count',
            $this->integer(11)->defaultValue(0)
        );
        $this->addColumn(
            '{{%admission_campaign}}',
            'limit_type',
            $this->string(255)->defaultValue('')
        );
        $this->addColumn(
            '{{%admission_campaign}}',
            'reception_allowed',
            $this->integer(11)->defaultValue(0)
        );

        \Yii::$app->db->schema->refresh();
    }

    


    public function safeDown() {
        $this->dropColumn(
            '{{%admission_campaign}}',
            'max_speciality_count'
        );
        $this->dropColumn(
            '{{%admission_campaign}}',
            'limit_type'
        );
        $this->dropColumn(
            '{{%admission_campaign}}',
            'reception_allowed'
        );

        \Yii::$app->db->schema->refresh();
    }
}
