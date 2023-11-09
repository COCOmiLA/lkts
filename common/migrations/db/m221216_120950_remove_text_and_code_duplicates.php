<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221216_120950_remove_text_and_code_duplicates extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->deleteCodeDuplicates();
        $this->deleteTextDuplicates();
    }

    private function deleteCodeDuplicates()
    {
        $code_setting_names = (new \yii\db\Query())
            ->select(['name'])
            ->from('{{%code_settings}}')
            ->distinct()
            ->column();
        foreach ($code_setting_names as $code_setting_name) {
            $code_settings = (new \yii\db\Query())
                ->select(['id', 'description'])
                ->from('{{%code_settings}}')
                ->where(['name' => $code_setting_name])
                ->all();
            if (count($code_settings) > 1) {
                $first_code = $code_settings[0];
                $code_settings = array_slice($code_settings, 1);
                foreach ($code_settings as $code_setting) {
                    if (mb_strlen((string)$code_setting['description']) > mb_strlen((string)$first_code['description'])) {
                        $first_code['description'] = $code_setting['description'];
                    }
                    $this->delete('{{%code_settings}}', ['id' => $code_setting['id']]);
                }
                $this->update('{{%code_settings}}', ['description' => $first_code['description']], ['id' => $first_code['id']]);
            }
        }
    }

    private function deleteTextDuplicates()
    {
        $text_settings = (new \yii\db\Query())
            ->select(['name', 'category', 'application_type', 'language'])
            ->from('{{%text_settings}}')
            ->groupBy(['name', 'category', 'application_type', 'language'])
            ->all();
        foreach ($text_settings as $text_setting_settings) {
            $text_settings = (new \yii\db\Query())
                ->select(['id', 'description', 'tooltip_description', 'default_value'])
                ->from('{{%text_settings}}')
                ->where($text_setting_settings)
                ->orderBy(['id' => SORT_ASC])
                ->all();
            if (count($text_settings) > 1) {
                $first_text_setting = $text_settings[0];
                $text_settings = array_slice($text_settings, 1);
                foreach ($text_settings as $text_setting) {
                    if (mb_strlen((string)$text_setting['description']) > mb_strlen((string)$first_text_setting['description'])) {
                        $first_text_setting['description'] = $text_setting['description'];
                    }
                    
                    if (mb_strlen((string)$text_setting['tooltip_description']) > mb_strlen((string)$first_text_setting['tooltip_description'])) {
                        $first_text_setting['tooltip_description'] = $text_setting['tooltip_description'];
                    }
                    
                    if (mb_strlen((string)$text_setting['default_value']) > mb_strlen((string)$first_text_setting['default_value'])) {
                        $first_text_setting['default_value'] = $text_setting['default_value'];
                    }
                    $this->delete('{{%text_settings}}', ['id' => $text_setting['id']]);
                }
                $this->update('{{%text_settings}}', [
                    'description' => $first_text_setting['description'],
                    'tooltip_description' => $first_text_setting['tooltip_description'],
                    'default_value' => $first_text_setting['default_value'],
                ], ['id' => $first_text_setting['id']]);
            }
        }
    }
}
