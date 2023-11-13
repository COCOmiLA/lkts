<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\modules\abiturient\models\PassportData;
use common\modules\abiturient\models\PersonalData;
use yii\helpers\ArrayHelper;







class m200608_125257_add_country_id_column_to_personal_data_table_drop_column_counry_id_in_passport_data_table extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        $this->addColumn('{{%personal_data}}', 'country_id', $this->integer());
        
        $this->createIndex(
            '{{%idx-dictionary_country}}',
            '{{%personal_data}}',
            'country_id'
        );

        
        $this->addForeignKey(
            '{{%fk-dictionary_country}}',
            '{{%personal_data}}',
            'country_id',
            '{{%dictionary_country}}',
            'id',
            'SET NULL'
        );

        
        $pers = PersonalData::find()->all();
        foreach ($pers as $per) {
            if (isset($per->questionary_id)) {
                $pass = PassportData::find()
                    ->where(['questionary_id' => $per->questionary_id])
                    ->one();
                if (isset($pass, $pass->country_id)) {
                    
                    $this->update(
                        'personal_data',
                        ['country_id' => $pass->country_id],
                        ['id' => $per->id]
                    );
                }
            }
        }
    }

    


    public function down()
    {
        
        foreach (PassportData::find()->all() as $pass) {
            $pass->country_id = ArrayHelper::getValue($pass, 'abiturientQuestionary.personalData.country_id');
            if ($pass->validate()) {
                $pass->save();
            } else {
                Yii::error('Невозможно откатить прошлые данные' . print_r($pass->errors, true), 'migration_error');
            }
        }

        
        $this->dropForeignKey(
            '{{%fk-dictionary_country}}',
            '{{%personal_data}}'
        );

        
        $this->dropIndex(
            '{{%idx-dictionary_country}}',
            '{{%personal_data}}'
        );

        $this->dropColumn('{{%personal_data}}', 'country_id');
    }
}
