<?php

use backend\models\FiltersSetting;
use common\components\Migration\MigrationWithDefaultOptions;




class m211119_120333_from_gly_to_fa extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $filter = FiltersSetting::find()->andWhere(['name' => 'statusBlock'])->one();
        $filter->label = '<span class="fa fa-lock"></span>';
        $filter->save(true, ['label']);
    }

    


    public function safeDown()
    {
        $filter = FiltersSetting::find()->andWhere(['name' => 'statusBlock'])->one();
        $filter->label = '<span class="glyphicon glyphicon-lock"></span>';
        $filter->save(true, ['label']);
    }
}
