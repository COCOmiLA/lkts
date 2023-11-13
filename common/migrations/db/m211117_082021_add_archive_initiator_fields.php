<?php

use yii\db\Migration;




class m211117_082021_add_archive_initiator_fields extends Migration
{
    


    public function safeUp()
    {
        $this->addColumn('{{%bachelor_application}}', 'archive_reason', $this->string());
        $this->addColumn('{{%bachelor_application}}', 'archived_by_user_id', $this->integer());
        $this->addColumn('{{%bachelor_application}}', 'archived_by_entrant_manager_id', $this->integer());

        $this->createIndex(
            '{{%idx-bachelor_application-archived_by_user_id}}',
            '{{%bachelor_application}}',
            'archived_by_user_id'
        );
        $this->createIndex(
            '{{%idx-bachelor_application-archived_by_entrant_manager_id}}',
            '{{%bachelor_application}}',
            'archived_by_entrant_manager_id'
        );
        $this->addForeignKey(
            '{{%fk-bachelor_application-archived_by_user_id}}',
            '{{%bachelor_application}}',
            'archived_by_user_id',
            '{{%user}}',
            'id',
            'NO ACTION'
        );
        $this->addForeignKey(
            '{{%fk-bachelor_application-archived_by_entrant_manager_id}}',
            '{{%bachelor_application}}',
            'archived_by_entrant_manager_id',
            '{{%entrant_manager}}',
            'id',
            'NO ACTION'
        );
        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropForeignKey('{{%fk-bachelor_application-archived_by_entrant_manager_id}}', '{{%bachelor_application}}');
        $this->dropForeignKey('{{%fk-bachelor_application-archived_by_user_id}}', '{{%bachelor_application}}');

        $this->dropIndex('{{%idx-bachelor_application-archived_by_entrant_manager_id}}', '{{%bachelor_application}}');
        $this->dropIndex('{{%idx-bachelor_application-archived_by_user_id}}', '{{%bachelor_application}}');

        $this->dropColumn('{{%bachelor_application}}', 'archived_by_user_id');
        $this->dropColumn('{{%bachelor_application}}', 'archived_by_entrant_manager_id');

        Yii::$app->db->schema->refresh();
    }

}
