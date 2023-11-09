<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\dictionary\AdmissionCategory;
use common\modules\abiturient\models\bachelor\BachelorPreferences;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;




class m220118_111528_alter_bvi_logic extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn(BachelorSpeciality::tableName(), 'is_without_entrance_tests', $this->boolean()->defaultValue(false));
        $this->addColumn(BachelorSpeciality::tableName(), 'bachelor_olympiad_id', $this->integer());
        $this->createIndex('idx_bach_spec_ol_id', BachelorSpeciality::tableName(), 'bachelor_olympiad_id');
        $this->addForeignKey('fk_bach_spec_ol_id', BachelorSpeciality::tableName(), 'bachelor_olympiad_id', BachelorPreferences::tableName(), 'id', 'SET NULL');

        Yii::$app->db->schema->refresh();

        Yii::$app->configurationManager->suspendUnspecifiedCodesError(true);
        $categoryAll = AdmissionCategory::findByUID(Yii::$app->configurationManager->getCode('category_all'));
        $category_olimpiad = AdmissionCategory::findByUID(Yii::$app->configurationManager->getCode('category_olympiad'));
        if ($category_olimpiad && $categoryAll) {
            
            $specs_to_restore = BachelorSpeciality::find()
                ->joinWith(['admissionCategory admission_category'])
                ->andWhere(['admission_category.ref_key' => $category_olimpiad->ref_key])
                ->all();

            foreach ($specs_to_restore as $spec) {
                if ($spec->preference && $spec->preference->isOlymp()) {
                    $spec->is_without_entrance_tests = true;
                    $spec->bachelor_olympiad_id = $spec->preference->id;
                    $spec->preference_id = null;
                }
                if ($spec->admission_category_id == $category_olimpiad->id) {
                    $spec->admission_category_id = $categoryAll->id;
                    $spec->category_code = $categoryAll->code;
                }
                $spec->save(false);
            }
        }
        Yii::$app->configurationManager->suspendUnspecifiedCodesError(false);
    }

    


    public function safeDown()
    {
        $this->dropForeignKey('fk_bach_spec_ol_id', BachelorSpeciality::tableName());
        $this->dropIndex('idx_bach_spec_ol_id', BachelorSpeciality::tableName());
        $this->dropColumn(BachelorSpeciality::tableName(), 'is_without_entrance_tests');
        $this->dropColumn(BachelorSpeciality::tableName(), 'bachelor_olympiad_id');
    }
}
