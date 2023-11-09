<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160607_112956_add_campaign_code_to_exam_date extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
         $this->addColumn('{{%exam_dates}}', 'campaign_code', $this->string(100)->notNull());
          Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
          $this->dropColumn('{{%exam_dates}}', 'campaign_code');
          Yii::$app->db->schema->refresh();
    }
}
