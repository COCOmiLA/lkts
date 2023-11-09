<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160718_101502_fix_misprint_chance_header extends MigrationWithDefaultOptions
{

    public function safeUp()
    {
        $this->dropColumn('{{%chance_list}}','learnFrom');
        $this->addColumn('{{%chance_list}}','learnForm', $this->string(1000));
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%chance_list}}','learnForm');
        $this->addColumn('{{%chance_list}}','learnFrom', $this->string(1000));
        
        Yii::$app->db->schema->refresh();
    }
}
