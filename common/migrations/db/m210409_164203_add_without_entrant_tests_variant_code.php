<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\dictionary\StoredReferenceType\StoredVariantOfRetestReferenceType;
use common\models\settings\CodeSetting;
use yii\helpers\ArrayHelper;




class m210409_164203_add_without_entrant_tests_variant_code extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $code = new CodeSetting();
        $code->attributes = [
            'description' => 'Код варианта перезачёта олимпиад "Без вступительных"',
            'name' => 'without_entrant_tests_variant',
            'value' => ArrayHelper::getValue(StoredVariantOfRetestReferenceType::findByName('БезВступительных'),'id') ?? ''
        ];
        $code->save();
    }

    


    public function safeDown()
    {
        $code = CodeSetting::findOne([
            'name' => 'without_entrant_tests_variant',
        ]);
        if ($code != null) {
            $code->delete();
        }
    }
}
