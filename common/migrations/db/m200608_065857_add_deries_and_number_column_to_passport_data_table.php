<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\modules\abiturient\models\PassportData;
use yii\helpers\ArrayHelper;




class m200608_065857_add_deries_and_number_column_to_passport_data_table extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        $this->addColumn('passport_data', 'series', $this->string());
        $this->addColumn('passport_data', 'number', $this->string());
        
        foreach (PassportData::find()->all() as $pass) {
            $pass->series = ArrayHelper::getValue($pass,'abiturientQuestionary.personalData.passport_series');
            $pass->number = ArrayHelper::getValue($pass,'abiturientQuestionary.personalData.passport_number');
            if($pass->validate()){
                $pass->save();
            } else {
                Yii::error('Невозможно сохранить прошлые данные' . print_r($pass->errors, true), 'migration_error');
            }
        }
    }

    


    public function down()
    {
        $this->dropColumn('passport_data', 'series');
        $this->dropColumn('passport_data', 'number');
    }}

