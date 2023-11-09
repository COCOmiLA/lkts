<?php

use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Query;




class m230113_085354_add_main_page_instruction extends MigrationWithDefaultOptions
{
    private const MAIN_PAGE_SETTING_TN = '{{%main_page_setting}}';
    private const MAIN_PAGE_INSTRUCTION_TEXT_TN = '{{%main_page_instruction_text}}';
    private const MAIN_PAGE_INSTRUCTION_HEADER_TN = '{{%main_page_instruction_header}}';

    private const INSERT_LIST = [
        1 => [
            'tn' => self::MAIN_PAGE_INSTRUCTION_HEADER_TN,
            'insert' => ['header' => 'Заполните анкету'],
        ],
        2 => [
            'tn' => self::MAIN_PAGE_INSTRUCTION_HEADER_TN,
            'insert' => ['header' => 'Выберите направления'],
        ],
        3 => [
            'tn' => self::MAIN_PAGE_INSTRUCTION_TEXT_TN,
            'insert' => ['paragraph' => 'Для выбора доступно ограниченное количество направлений подготовки (максимум {MAX_COUNT})'],
        ],
        4 => [
            'tn' => self::MAIN_PAGE_INSTRUCTION_HEADER_TN,
            'insert' => ['header' => 'Когда заявление проверят и примут или отклонят, вы получите уведомление по электронной почте'],
        ],
    ];

    


    public function safeUp()
    {
        foreach (array_keys(self::INSERT_LIST) as $i) {
            $this->insert(
                self::MAIN_PAGE_SETTING_TN,
                [
                    'number' => $i,

                    'created_at' => time(),
                    'updated_at' => time(),
                ]
            );
        }
        $mainPageSettings = (new Query())
            ->select(['id', 'number'])
            ->from(self::MAIN_PAGE_SETTING_TN)
            ->all();
        foreach ($mainPageSettings as $mainPageSetting) {
            if (!isset(self::INSERT_LIST[$mainPageSetting['number']])) {
                continue;
            }

            [
                'tn' => $tn,
                'insert' => $insert,
            ] = self::INSERT_LIST[$mainPageSetting['number']];

            $this->insert(
                $tn,
                array_merge(
                    [
                        'main_page_setting_id' => $mainPageSetting['id'],

                        'created_at' => time(),
                        'updated_at' => time(),
                    ],
                    $insert
                )
            );
        }

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->delete(self::MAIN_PAGE_SETTING_TN, ['>', 'id', 0]);
        $this->delete(self::MAIN_PAGE_INSTRUCTION_TEXT_TN, ['>', 'id', 0]);
        $this->delete(self::MAIN_PAGE_INSTRUCTION_HEADER_TN, ['>', 'id', 0]);

        Yii::$app->db->schema->refresh();
    }
}
