<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200715_082852_create_comments_coming extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('comments_coming', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'author_id' => $this->integer()->comment('id автора комментария'),
            'comment' => $this->text()->comment('Комментарий'),
            'answer' => $this->text()->comment('Ответ на комментарий'),
            'bachelor_application_id' => $this->integer()->comment('id заявления'),
        ]);

        $this->addCommentOnTable('comments_coming', 'Комментарии поступающего');

        $this->addForeignKey(
            'fk_comments_coming_bachelor_application',
            'comments_coming',
            'bachelor_application_id',
            'bachelor_application',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_comments_coming_user',
            'comments_coming',
            'author_id',
            'user',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    


    public function safeDown()
    {
        $this->dropForeignKey('fk_comments_coming_bachelor_application', 'comments_coming');
        $this->dropForeignKey('fk_comments_coming_user', 'comments_coming');
        $this->dropTable('comments_coming');
    }
}
