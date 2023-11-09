<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200701_112715_add_deleted_column_to_attachment_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%attachment}}', 'deleted', $this->boolean()->null());
        \common\models\Attachment::updateAll(['deleted' => 0]);
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%attachment}}', 'deleted');
    }
}
