<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m211124_090523_add_parent_draft_id_column_to_bachelor_applications extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%bachelor_application}}', 'parent_draft_id', $this->integer());

        $this->createIndex(
            '{{%idx-bachelor_application-parent_draft_id}}',
            '{{%bachelor_application}}',
            'parent_draft_id'
        );

        $this->addForeignKey(
            '{{%fk-bachelor_application-parent_draft_id}}',
            '{{%bachelor_application}}',
            'parent_draft_id',
            '{{%bachelor_application}}',
            'id',
            'NO ACTION'
        );

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropForeignKey(
            '{{%fk-bachelor_application-parent_draft_id}}',
            '{{%bachelor_application}}'
        );


        $this->dropIndex(
            '{{%idx-bachelor_application-parent_draft_id}}',
            '{{%bachelor_application}}'
        );

        $this->dropColumn('{{%bachelor_application}}', 'parent_draft_id');

        Yii::$app->db->schema->refresh();

    }
}
