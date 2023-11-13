<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\components\RegulationRelationManager;
use common\models\Attachment;
use common\models\UserRegulation;




class m220302_135209_many_to_many_for_attachments_regulations extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->dropForeignKey(
            '{{%fk-user_regulation-attachment_id}}',
            '{{%user_regulation}}'
        );

        $this->dropIndex(
            '{{%idx-user_regulation-attachment_id}}',
            '{{%user_regulation}}'
        );
        $this->dropColumn('{{%user_regulation}}', 'attachment_id');

        $this->createTable('{{%attachments-user_regulations}}', [
            'id' => $this->primaryKey(),
            'attachment_id' => $this->integer()->notNull(),
            'user_regulation_id' => $this->integer()->notNull(),
        ]);
        $this->createIndex('idx-chments-user-attachment', '{{%attachments-user_regulations}}', 'attachment_id');
        $this->createIndex('idx-chments-user-regulation', '{{%attachments-user_regulations}}', 'user_regulation_id');

        $this->addForeignKey('fk_attachments-user_regulations_attachment_id', '{{%attachments-user_regulations}}', 'attachment_id', '{{%attachment}}', 'id', 'restrict', 'restrict');
        $this->addForeignKey('fk_attachments-user_regulations_user_regulation_id', '{{%attachments-user_regulations}}', 'user_regulation_id', '{{%user_regulation}}', 'id', 'restrict', 'restrict');
        Yii::$app->db->schema->refresh();

        $user_regulations = UserRegulation::find()->all();
        foreach ($user_regulations as $userRegulation) {
            $attachments = $this->getUserRegulationAttachments($userRegulation);
            foreach ($attachments as $attachment) {
                $userRegulation->link('rawAttachments', $attachment);
            }
        }
    }

    


    public function safeDown()
    {
        $this->addColumn('{{%user_regulation}}', 'attachment_id', $this->integer());

        $this->createIndex(
            '{{%idx-user_regulation-attachment_id}}',
            '{{%user_regulation}}',
            'attachment_id'
        );

        $this->addForeignKey(
            '{{%fk-user_regulation-attachment_id}}',
            '{{%user_regulation}}',
            'attachment_id',
            '{{%attachment}}',
            'id',
            'NO ACTION'
        );
        $this->dropForeignKey('fk_attachments-user_regulations_attachment_id', '{{%attachments-user_regulations}}');
        $this->dropForeignKey('fk_attachments-user_regulations_user_regulation_id', '{{%attachments-user_regulations}}');

        $this->dropIndex('idx-chments-user-attachment', '{{%attachments-user_regulations}}');
        $this->dropIndex('idx-chments-user-regulation', '{{%attachments-user_regulations}}');

        $this->dropTable('{{%attachments-user_regulations}}');
    }

    private function getUserRegulationAttachments(UserRegulation $userRegulation): array
    {
        if ($userRegulation->regulation->attachmentType !== null && $userRegulation->owner !== null) {
            $query = Attachment::find()
                ->where([
                    'attachment.attachment_type_id' => $userRegulation->regulation->attachment_type,
                    'attachment.deleted' => 0
                ]);

            if (in_array($userRegulation->regulation->related_entity, [
                RegulationRelationManager::RELATED_ENTITY_QUESTIONARY,
            ], true)) {

                $query
                    ->joinWith('abiturientQuestionary q')
                    ->andWhere([
                        'q.user_id' => $userRegulation->owner_id
                    ]);

            } else if (in_array($userRegulation->regulation->related_entity, [
                RegulationRelationManager::RELATED_ENTITY_REGISTRATION,
            ], true)) {
                $query
                    ->andWhere([
                        'attachment.owner_id' => $userRegulation->owner_id
                    ]);
            } else {
                $query->andWhere([
                    'attachment.application_id' => $userRegulation->application_id
                ]);

            }
            return $query->all();
        }
        return [];
    }
}
