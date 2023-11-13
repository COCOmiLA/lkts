<?php

use common\components\Migration\SafeMigration;




class m230608_080827_delete_old_text_settings extends SafeMigration
{
    private const TN = '{{%text_settings}}';

    


    public function safeUp()
    {
        $this->delete(
            self::TN,
            ['LIKE', 'name', '%_hint', false]
        );
    }

    


    public function safeDown()
    {
        $this->insert(
            self::TN,
            [
                'name'                => 'first_applist_hint',
                'value'               => 'Заполните анкету',
                'category'            => 'index',
                'description'         => 'Пункт 1 инструкции по подаче заявления',
                'default_value'       => 'Заполните анкету',
                'tooltip_description' => 'Отображается на стартовой странице личного кабинета поступающего перед заполнением анкеты.',
            ]
        );
        $this->insert(
            self::TN,
            [
                'name'                => 'second_applist_hint',
                'value'               => 'Выберите направления для поступления (максимум {MAX_COUNT})',
                'category'            => 'index',
                'description'         => 'Пункт 2 инструкции по подаче заявления',
                'default_value'       => 'Выберите направления для поступления (максимум {MAX_COUNT})',
                'tooltip_description' => 'Отображается на стартовой странице личного кабинета поступающего перед заполнением анкеты.',
            ]
        );
        $this->insert(
            self::TN,
            [
                'name'                => 'third_applist_hint',
                'value'               => 'Когда заявление проверят и примут или отклонят, вы получите уведомление по электронной почте',
                'category'            => 'index',
                'description'         => 'Пункт 3 инструкции по подаче заявления',
                'default_value'       => 'Когда заявление проверят и примут или отклонят, вы получите уведомление по электронной почте',
                'tooltip_description' => 'Отображается на стартовой странице личного кабинета поступающего перед заполнением анкеты.',
            ]
        );
    }
}
