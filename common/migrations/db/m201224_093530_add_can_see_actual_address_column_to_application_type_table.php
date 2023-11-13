<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\modules\abiturient\models\bachelor\ApplicationType;




class m201224_093530_add_can_see_actual_address_column_to_application_type_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%application_type}}', 'can_see_actual_address', $this->boolean()->null());
        $this->addColumn('{{%application_type}}', 'required_actual_address', $this->boolean()->null());

        ApplicationType::updateAll([
            'can_see_actual_address' => false,
            'required_actual_address' => false
        ]);
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%application_type}}', 'can_see_actual_address');
        $this->dropColumn('{{%application_type}}', 'required_actual_address');
    }
}
