<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210611_120032_hidden_default_value extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->alterColumn(\common\models\AttachmentType::tableName(), 'hidden', $this->boolean()->defaultValue(false));
        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->alterColumn(\common\models\AttachmentType::tableName(), 'hidden', $this->boolean());
        Yii::$app->db->schema->refresh();
    }

}
