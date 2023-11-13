<?php
namespace common\components\migrations\traits;


use common\components\AppUpdate;
use Yii;
use yii\helpers\Console;

trait DictionaryUpdateTrait
{
    private function updateDictionary($dictionary)
    {
        $name = AppUpdate::DICTIONARY_UPDATE['loadDictionaryCompetitiveGroupEntranceTests'];
        $dictionaryManager = \Yii::$app->dictionaryManager;
        $updateResult = call_user_func(array($dictionaryManager, $dictionary));
        if ($updateResult[0] != 1) {
            Yii::error(
                'Ошибка обновления справочника: "' . $name . '".' . PHP_EOL . print_r($updateResult, true),
                'm210427_052336_old_data_recovery_for_bachelor_egeresult'
            );
            $error = Console::ansiFormat('Внимание!!!', [Console::FG_BLACK, Console::BG_RED]);
            echo $error . ' Ошибка обновления справочника: "' . $name . '".' . PHP_EOL . print_r($updateResult, true);

            return false;
        }
        return true;
    }
}