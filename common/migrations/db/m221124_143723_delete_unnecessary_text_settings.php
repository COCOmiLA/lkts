<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221124_143723_delete_unnecessary_text_settings extends MigrationWithDefaultOptions
{
    private const TN = '{{%text_settings}}';

    


    public function safeUp()
    {
        $this->delete(self::TN, ['category' => 'dormitory']);
    }

    


    public function safeDown()
    {
        $this->insert(
            self::TN,
            [
                'name' => 'dormitory_top_text',
                'description' => 'Текст c верху в Общежитиях',
                'value' => '',
                'category' => 6,
            ]
        );

        $this->insert(
            self::TN,
            [
                'name' => 'dormitory_bottom_text',
                'description' => 'Текст снизу в Общежитиях',
                'value' => '',
                'category' => 6,
            ]
        );

        $this->insert(
            self::TN,
            [
                'name' => 'dormitory_limitations',
                'description' => 'Текст о существующих ограничениях на запись в общежитие',
                'value' => 'Запись в общежитие возможна только после предоставления оригиналов документов в приёмную комиссию и только для поступающих проживающих вне Москвы и Московской области.',
                'category' => 6,
            ]
        );
    }
}
