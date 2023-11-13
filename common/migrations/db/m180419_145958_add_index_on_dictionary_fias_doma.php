<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m180419_145958_add_index_on_dictionary_fias_doma extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createIndex(
            'idx_code_dictionary_fias_doma',
            'dictionary_fias_doma',
            'code'
        );
        \Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropIndex(
            'idx_code_dictionary_fias_doma',
            'dictionary_fias_doma'
        );
        \Yii::$app->db->schema->refresh();
    }


}
