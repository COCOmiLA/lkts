<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160607_125002_add_application_code_to_exam_register extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
         $this->addColumn('{{%exam_register}}', 'application_code', $this->string(100));
          Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
          $this->dropColumn('{{%exam_register}}', 'application_code');
          Yii::$app->db->schema->refresh();
    }
}
