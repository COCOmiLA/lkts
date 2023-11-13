<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\EmptyCheck;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use yii\helpers\ArrayHelper;




class m210514_154315_recover_multiple_educations extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        foreach (BachelorApplication::find()->with(['educations'])->all() as $app) {
            $app_specialities = BachelorSpeciality::find()->where(['application_id' => $app->id])->all();
            foreach ($app_specialities as $bachelor_spec) {
                if (EmptyCheck::isEmpty($bachelor_spec->education_id)) {
                    $bachelor_spec->education_id = ArrayHelper::getValue($app, 'educations.0.id');
                    $bachelor_spec->save(true, ['education_id']);
                }
            }
        }
    }

    


    public function safeDown()
    {
        BachelorSpeciality::updateAll(['education_id' => null]);
    }

}
