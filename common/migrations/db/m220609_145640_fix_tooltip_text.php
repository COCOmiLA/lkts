<?php

use common\components\LikeQueryManager;
use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Query;
use yii\helpers\Console;




class m220609_145640_fix_tooltip_text extends MigrationWithDefaultOptions
{
    private const TN = '{{%text_settings}}';
    


    public function safeUp()
    {
        $badTexts = (new Query())
            ->select(['id'])
            ->from(self::TN)
            ->where([LikeQueryManager::getActionName(), 'tooltip_description', 'личного кабинет поступающего'])
            ->all();

        if (empty($badTexts)) {
            echo Console::ansiFormat(
                'Не найдены данные требующие обновления',
                [Console::BG_GREEN, Console::FG_BLACK]
            ) . PHP_EOL;

            return true;
        }

        foreach ($badTexts as $badText) {
            $this->update(
                self::TN,
                ['tooltip_description' => 'Отображается на стартовой странице личного кабинета поступающего перед заполнением анкеты.'],
                ['id' => $badText['id']]
            );
        }
    }

    


    public function safeDown()
    {
        $badTexts = (new Query())
            ->select(['id'])
            ->from(self::TN)
            ->where(['=', 'tooltip_description', 'Отображается на стартовой странице личного кабинета поступающего перед заполнением анкеты.'])
            ->all();

        if (empty($badTexts)) {
            echo Console::ansiFormat(
                'Не найдены данные требующие восстановления',
                [Console::BG_GREEN, Console::FG_BLACK]
            ) . PHP_EOL;

            return true;
        }

        foreach ($badTexts as $badText) {
            $this->update(
                self::TN,
                ['tooltip_description' => 'Отображается на стартовой странице личного кабинет поступающего перед заполнением анкеты.'],
                ['id' => $badText['id']]
            );
        }
    }
}
