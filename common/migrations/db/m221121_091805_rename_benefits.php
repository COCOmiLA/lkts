<?php

use common\components\IndependentQueryManager\IndependentQueryManager;
use common\components\Migration\MigrationWithDefaultOptions;




class m221121_091805_rename_benefits extends MigrationWithDefaultOptions
{

    


    public function safeUp()
    {
        $col = Yii::$app->db->quoteColumnName('description');
        $needle = IndependentQueryManager::quoteString("Льготы и преимущественные права");
        $replace = IndependentQueryManager::quoteString("Особые условия поступления");
        $this->update(\common\models\settings\TextSetting::tableName(), ['description' => new \yii\db\Expression("REPLACE({$col}, {$needle}, {$replace})")]);
        $col = Yii::$app->db->quoteColumnName('tooltip_description');
        $this->update(\common\models\settings\TextSetting::tableName(), ['tooltip_description' => new \yii\db\Expression("REPLACE({$col}, {$needle}, {$replace})")]);
    }

}
