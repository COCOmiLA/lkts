<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m190429_082145_in__dictionary_fias_doma__add_columnes__created_at__and__updated_at_ extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {

    }

    


    public function safeDown()
    {
        echo "m190429_082145_in__dictionary_fias_doma__add_columnes__created_at__and__updated_at_ cannot be reverted.\n";

        return false;
    }

    public function up()
    {
        $this->addColumn('{{%dictionary_fias_doma}}', 'created_at', $this->integer());
        $this->addColumn('{{%dictionary_fias_doma}}', 'updated_at', $this->integer());

        Yii::$app->db->schema->refresh();
    }

    public function down()
    {
        $this->dropColumn('{{%dictionary_fias_doma}}', 'created_at');
        $this->dropColumn('{{%dictionary_fias_doma}}', 'updated_at');

        Yii::$app->db->schema->refresh();
    }
}
