<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210814_095445_add_timestamps_to_specs_and_ege extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%bachelor_speciality}}', 'created_at', $this->integer(11));
        $this->addColumn('{{%bachelor_speciality}}', 'updated_at', $this->integer(11));

        $this->addColumn('{{%bachelor_egeresult}}', 'created_at', $this->integer(11));
        $this->addColumn('{{%bachelor_egeresult}}', 'updated_at', $this->integer(11));
        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%bachelor_speciality}}', 'created_at');
        $this->dropColumn('{{%bachelor_speciality}}', 'updated_at');

        $this->dropColumn('{{%bachelor_egeresult}}', 'created_at');
        $this->dropColumn('{{%bachelor_egeresult}}', 'updated_at');
        Yii::$app->db->schema->refresh();
    }

}
