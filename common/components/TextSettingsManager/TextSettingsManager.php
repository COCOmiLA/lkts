<?php

namespace common\components\TextSettingsManager;

use common\models\settings\TextSetting;
use Yii;
use yii\helpers\ArrayHelper;

class TextSettingsManager
{
    const BATCH_SIZE = 1000;

    



    public static function getDefaultValueHash($model): string
    {
        return crc32("{$model['language']}{$model['name']}");
    }

    public static function getDefaultValues(): array
    {
        
        $defaults = TextSetting::findAll([
            'application_type' => TextSetting::APPLICATION_TYPE_DEFAULT,
        ]);

        return ArrayHelper::index($defaults, function (TextSetting $el) {
            return static::getDefaultValueHash($el);
        });
    }

    public static function isDefaultSettingsChanged(): bool
    {
        $table = Yii::$app->db->schema->getTableSchema('{{%text_settings}}');
        if (!isset($table->columns['default_value'])) {
            return false;
        }

        $map = static::getDefaultValues();
        $to_check = TextSetting::find();
        foreach ($to_check->each(static::BATCH_SIZE) as $text_setting) {
            $hash = static::getDefaultValueHash($text_setting);
            if (!isset($map[$hash])) {
                return true;
            }

            if ($text_setting->value !== $map[$hash]->default_value) {
                return true;
            }
        }

        return false;
    }

    public static function resetToDefaultSettings(): bool
    {
        $success = true;
        $map = static::getDefaultValues();
        $to_update = TextSetting::find();

        foreach ($to_update->each(static::BATCH_SIZE) as $text_setting) {
            $hash = static::getDefaultValueHash($text_setting);
            if (!isset($map[$hash])) {
                continue;
            }

            $text_setting->value = $map[$hash]->default_value;
            if (!$text_setting->save(true, ['value'])) {
                $success = false;
            }
        }

        Yii::$app->configurationManager->resetTextCache();

        return $success;
    }
}
