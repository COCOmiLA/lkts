<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m190507_134949_update__attachment_type_ extends MigrationWithDefaultOptions
{
    public function up()
    {
        $this->update('{{%attachment_type}}', ['name' => 'Разворот паспорта с персональными данными'], ['name' => 'Первая страница паспорта']);
        $this->update('{{%attachment_type}}', ['name' => 'Разворот паспорта с отметками о регистрации'], ['name' => 'Страница сведений о регистрации']);
    }

    public function down()
    {
        $this->update('{{%attachment_type}}', ['name' => 'Первая страница паспорта'], ['name' => 'Разворот паспорта с персональными данными']);
        $this->update('{{%attachment_type}}', ['name' => 'Страница сведений о регистрации'], ['name' => 'Разворот паспорта с отметками о регистрации']);
    }
}
