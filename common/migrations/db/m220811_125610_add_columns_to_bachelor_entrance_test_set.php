<?php

use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Query;
use yii\helpers\Console;




class m220811_125610_add_columns_to_bachelor_entrance_test_set extends MigrationWithDefaultOptions
{
    private const EGE_RESULT_TN = '{{%bachelor_egeresult}}';
    private const TEST_SET_TN   = '{{%bachelor_entrance_test_set}}';

    


    public function safeUp()
    {
        $this->addColumn(self::TEST_SET_TN, 'entrance_test_junction', $this->string(32)->defaultValue(null));

        $this->createIndex(
            'IDX-bachelor_entrance_test_set-for-entrance_test_junction',
            self::TEST_SET_TN,
            ['entrance_test_junction']
        );


        $this->addColumn(self::EGE_RESULT_TN, 'entrance_test_junction', $this->string(32)->defaultValue(null));

        $this->createIndex(
            'IDX-bachelor_egeresult-for-entrance_test_junction',
            self::EGE_RESULT_TN,
            ['entrance_test_junction']
        );

        Yii::$app->db->schema->refresh();

        $this->updateFromOldData();
    }

    


    public function safeDown()
    {
        $this->dropIndex('IDX-bachelor_egeresult-for-entrance_test_junction',         self::EGE_RESULT_TN);
        $this->dropIndex('IDX-bachelor_entrance_test_set-for-entrance_test_junction', self::TEST_SET_TN);

        $this->dropColumn(self::TEST_SET_TN,   'entrance_test_junction');
        $this->dropColumn(self::EGE_RESULT_TN, 'entrance_test_junction');

        Yii::$app->db->schema->refresh();
    }

    




    private function jointEntranceTestData(array $testSet): string
    {
        return md5(
            "{$testSet['discipline_reference_uid']} {$testSet['discipline_form_reference_uid']} {$testSet['child_discipline_reference_uid']}"
        );
    }

    




    private function printTotalCount(array $testSets): void
    {
        echo Console::ansiFormat(
            'Конвертирование ',
            [Console::BG_BLACK, Console::FG_CYAN]
        );
        echo Console::ansiFormat(
            count($testSets),
            [Console::BG_PURPLE, Console::FG_BLACK]
        );
        echo Console::ansiFormat(
            ' данных к новой структуре данных',
            [Console::BG_BLACK, Console::FG_CYAN]
        );
        echo "\n\n";
    }

    


    private function getDataForUpdate(): array
    {
        $tnTestSet = self::TEST_SET_TN;
        $tnEgeResult = self::EGE_RESULT_TN;
        $tnDiscipline = '{{%discipline_reference_type}}';
        $tnDisciplineForm = '{{%discipline_form_reference_type}}';
        $tnChildDiscipline = '{{%child_discipline_reference_type}}';

        return (new Query())
            ->select([
                "{$tnTestSet}.id                    AS test_set_id",
                "{$tnEgeResult}.id                  AS ege_result_id",
                "{$tnDiscipline}.reference_uid      AS discipline_reference_uid",
                "{$tnDisciplineForm}.reference_uid  AS discipline_form_reference_uid",
                "{$tnChildDiscipline}.reference_uid AS child_discipline_reference_uid",
            ])
            ->from(self::TEST_SET_TN)
            ->leftJoin(
                $tnEgeResult,
                "{$tnTestSet}.bachelor_egeresult_id = {$tnEgeResult}.id"
            )
            ->leftJoin(
                $tnDiscipline,
                "{$tnEgeResult}.cget_discipline_id = {$tnDiscipline}.id"
            )
            ->leftJoin(
                $tnDisciplineForm,
                "{$tnEgeResult}.cget_exam_form_id = {$tnDisciplineForm}.id"
            )
            ->leftJoin(
                [$tnChildDiscipline => $tnDiscipline],
                "{$tnEgeResult}.cget_child_discipline_id = {$tnChildDiscipline}.id"
            )
            ->all();
    }

    


    private function updateFromOldData(): void
    {
        $testSets = $this->getDataForUpdate();

        if (!$testSets) {
            return;
        }

        $this->printTotalCount($testSets);

        $updatedEgeIds = [];
        foreach ($testSets as $i => $testSet) {
            $jointEntranceTest = $this->jointEntranceTestData($testSet);

            $this->update(
                self::TEST_SET_TN,
                [
                    'updated_at' => time(),
                    'entrance_test_junction' => $jointEntranceTest,
                ],
                ['id' => $testSet['test_set_id']]
            );

            if (!in_array($testSet['ege_result_id'], $updatedEgeIds)) {
                $updatedEgeIds[] = $testSet['ege_result_id'];

                $this->update(
                    self::EGE_RESULT_TN,
                    [
                        'updated_at' => time(),
                        'entrance_test_junction' => $jointEntranceTest,
                    ],
                    ['id' => $testSet['ege_result_id']]
                );
            }
        }
    }
}
