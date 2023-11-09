<?php

use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Query;
use yii\helpers\Console;




class m210823_075629_convert_single_education_to_multiply_for_CGET extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $brockenSpecialities = (new Query())
            ->select([
                'bachelor_speciality.id',
                'education_data.education_type_id',
                'cget_entrance_test_set.entrance_test_set_ref_id',
                'cget_entrance_test_set.dictionary_competitive_group_entrance_test_id'
            ])
            ->from('bachelor_speciality')
            ->leftJoin(
                'cget_entrance_test_set',
                'bachelor_speciality.cget_entrance_test_set_id = cget_entrance_test_set.id'
            )
            ->leftJoin(
                'education_data',
                'bachelor_speciality.education_id = education_data.id'
            )
            ->where(['IS', 'cget_entrance_test_set.education_type_ref_id', null])
            ->andWhere(['IS NOT', 'bachelor_speciality.education_id', null])
            ->andWhere(['cget_entrance_test_set.archive' => true])
            ->all();

        $successCounter = 0;
        if (!empty($brockenSpecialities)) {
            Yii::$app->db->schema->refresh();

            foreach ($brockenSpecialities as $spec) {
                $actualCgetEntranceTestSet = (new Query())
                    ->select(['*'])
                    ->from('cget_entrance_test_set')
                    ->where([
                        'archive' => false,
                        'education_type_ref_id' => $spec['education_type_id'],
                        'entrance_test_set_ref_id' => $spec['entrance_test_set_ref_id'],
                        'dictionary_competitive_group_entrance_test_id' => $spec['dictionary_competitive_group_entrance_test_id'],
                    ])
                    ->one();

                if (empty($actualCgetEntranceTestSet)) {
                    $warning = Console::ansiFormat('Внимание!!!', [Console::FG_BLACK, Console::BG_YELLOW]);
                    echo "\n{$warning} Не найдена актуальная запись набора ВИ:\n" . print_r($spec, true) . "\n";

                    continue;
                }

                $this->update(
                    'bachelor_speciality',
                    ['bachelor_speciality.cget_entrance_test_set_id' => $actualCgetEntranceTestSet['id']],
                    ['bachelor_speciality.id' => $spec['id']]
                );

                $successCounter++;
            }
        }

        if ($successCounter > 0) {
            $message = "\nБыло успешно актуализировано '{$successCounter}' направлений подготовки\n";
            echo Console::ansiFormat($message, [Console::FG_BLACK, Console::BG_GREEN]);
        } else {
            $message = "\nНаправлений подготовки в актуализации не нуждаются\n";
            echo Console::ansiFormat($message, [Console::FG_BLACK, Console::BG_GREEN]);
        }
        return true;
    }

    


    public function safeDown()
    {
    }
}
