<?php

use common\components\IndependentQueryManager\IndependentQueryManager;
use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;
use common\models\settings\TextSetting;




class m221103_070649_alter_master_system_name extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        $to_replace = [
            [TextSetting::tableName(), 'load_from_1c_info', 'description', 'обновление из 1С ', 'обновление из Информационной системы вуза '],
            [TextSetting::tableName(), 'load_from_1c_info', 'value', 'обновление из 1С ', 'обновление из Информационной системы вуза '],
            [TextSetting::tableName(), 'load_from_1c_info', 'value', 'Получение информации из "1С:Университет ПРОФ"', 'Получение информации из Информационной системы вуза'],
            [TextSetting::tableName(), 'load_from_1c_info', 'value', 'из \"1С:Университет ПРОФ\"', 'из Информационной системы вуза'],
            [CodeSetting::tableName(), 'app_sending_type', 'description', 'с 1С ', 'с Информационной системой вуза '],
            [TextSetting::tableName(), 'questionary__create_from_1C', 'description', 'создана из 1С', 'создана из Информационной системы вуза'],
            [TextSetting::tableName(), 'questionary__create_from_1C', 'tooltip_description', 'восстановил анкету из 1С:Университет ПРОФ', 'восстановил анкету из Информационной системы вуза '],
            [TextSetting::tableName(), 'need_update_app_from_one_s', 'description', 'наличии в 1С:Университет ПРОФ', 'наличии в Информационной системе вуза'],
            [TextSetting::tableName(), 'need_update_app_from_one_s', 'value', 'В 1С:Университет ПРОФ присутствует', 'В Информационной системе вуза присутствует'],
            [TextSetting::tableName(), 'need_update_questionary_from_one_s', 'description', 'наличии в 1С:Университет ПРОФ', 'наличии в Информационной системе вуза'],
            [TextSetting::tableName(), 'need_update_questionary_from_one_s', 'value', 'в Личном кабинете и 1С:Университет ПРОФ', 'в Личном кабинете и Информационной системе вуза'],

            [TextSetting::tableName(), null, null, 'подана в 1С:Университет ПРОФ', 'подана в Информационную систему вуза'],
            [TextSetting::tableName(), null, null, 'проверку в 1С:Университет ПРОФ', 'проверку в Информационной системе вуза'],
            [TextSetting::tableName(), null, null, 'отправлено в 1С:Университет ПРОФ', 'отправлено в Информационную систему вуза'],
            [TextSetting::tableName(), null, null, 'информации из "1С:Университет ПРОФ"', 'информации из Информационной системы вуза'],
            [TextSetting::tableName(), null, null, 'Отклонено 1С', 'Отклонено Информационной системой вуза'],
            [TextSetting::tableName(), null, null, 'отклонено 1С', 'отклонено Информационной системой вуза'],
            [TextSetting::tableName(), null, null, 'отклонена 1С', 'отклонена Информационной системой вуза'],
            [TextSetting::tableName(), null, null, 'отклонены 1С', 'отклонены Информационной системой вуза'],
            [TextSetting::tableName(), null, null, 'В 1С:Университет ПРОФ присутст', 'В Информационной системе вуза присутст'],
            [TextSetting::tableName(), null, null, 'наличии в 1С:Университет ПРОФ', 'наличии в Информационной системе вуза'],
            [TextSetting::tableName(), null, null, 'проверку 1С:Университет ПРОФ', 'проверку в Информационной системе вуза'],
            [TextSetting::tableName(), null, null, 'подано в 1С:Университет ПРОФ', 'подано в Информационную систему вуза'],
            [TextSetting::tableName(), null, null, 'подана в 1С', 'подана в Информационную систему вуза'],
            [TextSetting::tableName(), null, null, 'сохранения в 1С', 'сохранения в Информационную систему вуза'],
            [TextSetting::tableName(), null, null, 'сохранении в 1С', 'сохранении в Информационную систему вуза'],
            [TextSetting::tableName(), null, null, 'Обновить из 1С', 'Обновить из Информационной системы вуза'],
            [TextSetting::tableName(), null, null, 'обновить заявление из 1С', 'обновить заявление из Информационной системы вуза'],
            [TextSetting::tableName(), null, null, 'принятого в 1С', 'принятого в Информационной системе вуза'],
            [TextSetting::tableName(), null, null, 'поданы в 1С', 'поданы в Информационную систему вуза'],
            [TextSetting::tableName(), null, null, 'подано в 1С', 'подано в Информационную систему вуза'],
        ];
        foreach ($to_replace as [$table, $name, $column, $needle, $replace]) {
            if (!$column) {
                $column = ['description', 'value', 'tooltip_description', 'default_value'];
            }
            if (!is_array($column)) {
                $column = [$column];
            }
            $needle = IndependentQueryManager::quoteString($needle);
            $replace = IndependentQueryManager::quoteString($replace);
            foreach ($column as $col) {
                $col = IndependentQueryManager::quoteEntity($col);
                $this->update($table, [$col => new \yii\db\Expression("REPLACE({$col}, {$needle}, {$replace})")], $name ? ['name' => $name] : []);
            }
        }
    }
}
