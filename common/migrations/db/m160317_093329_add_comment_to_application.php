<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160317_093329_add_comment_to_application extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->addColumn('{{%bachelor_application}}', 'moderator_comment', $this->text());
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
         $this->dropColumn('{{%bachelor_application}}', 'moderator_comment');
        Yii::$app->db->schema->refresh();
    }
}
