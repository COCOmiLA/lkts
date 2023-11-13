<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200627_123403_add_hiddent_column_to_attachment_type_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%attachment_type}}', 'hidden', $this->boolean()->null());
        \common\models\AttachmentType::updateAll(['hidden'=>0]);
        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%attachment_type}}', 'hidden');
        Yii::$app->db->schema->refresh();
    }
}
