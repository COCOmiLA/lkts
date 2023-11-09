<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200701_072955_add_language_code_column_to_presonal_data_table extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        $this->addColumn('{{%personal_data}}', 'language_code', $this->string()->null());
        Yii::$app->db->schema->refresh();
    }

    


    public function down()
    {
        $this->dropColumn('{{%personal_data}}', 'language_code');
        Yii::$app->db->schema->refresh();
    }
}
