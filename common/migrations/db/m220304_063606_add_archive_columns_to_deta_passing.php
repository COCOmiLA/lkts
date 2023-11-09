<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220304_063606_add_archive_columns_to_deta_passing extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%bachelor_date_passing_entrance_test}}', 'archived_at', $this->integer()->defaultValue(null));
        $this->addColumn('{{%bachelor_date_passing_entrance_test}}', 'archive', $this->boolean()->defaultValue(false));

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%bachelor_date_passing_entrance_test}}', 'archived_at');
        $this->dropColumn('{{%bachelor_date_passing_entrance_test}}', 'archive');

        Yii::$app->db->schema->refresh();
    }
}
