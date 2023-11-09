<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160426_064427_remove_unused_tables extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->dropForeignKey('fk_article_attachment_article', '{{%article_attachment}}');
        $this->dropForeignKey('fk_article_author', '{{%article}}');
        $this->dropForeignKey('fk_article_updater', '{{%article}}');
        $this->dropForeignKey('fk_article_category', '{{%article}}');
        $this->dropForeignKey('fk_article_category_section', '{{%article_category}}');

        $this->dropTable('{{%article_attachment}}');
        $this->dropTable('{{%article}}');
        $this->dropTable('{{%article_category}}');
        
        $this->dropTable('{{%page}}');
        $this->dropTable('{{%widget_menu}}');
        $this->dropTable('{{%widget_text}}');
        
        $this->dropForeignKey('fk_item_carousel', '{{%widget_carousel_item}}');
        $this->dropTable('{{%widget_carousel_item}}');
        $this->dropTable('{{%widget_carousel}}');
        $this->dropTable('{{%file_storage_item}}');
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%article_category}}', [
            'id' => $this->primaryKey(),
            'slug' => $this->string(1024)->notNull(),
            'title' => $this->string(512)->notNull(),
            'body' => $this->text(),
            'parent_id' => $this->integer(),
            'status' => $this->smallInteger()->notNull()->defaultValue(0),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);

        $this->createTable('{{%article}}', [
            'id' => $this->primaryKey(),
            'slug' => $this->string(1024)->notNull(),
            'title' => $this->string(512)->notNull(),
            'body' => $this->text()->notNull(),
            'view' => $this->string(),
            'category_id' => $this->integer(),
            'thumbnail_base_url' => $this->string(1024),
            'thumbnail_path' => $this->string(1024),
            'author_id' => $this->integer(),
            'updater_id' => $this->integer(),
            'status' => $this->smallInteger()->notNull()->defaultValue(0),
            'published_at' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);

        $this->createTable('{{%article_attachment}}', [
            'id' => $this->primaryKey(),
            'article_id' => $this->integer()->notNull(),
            'path' => $this->string()->notNull(),
            'base_url' => $this->string(),
            'type' => $this->string(),
            'size' => $this->integer(),
            'name' => $this->string(),
            'created_at' => $this->integer()
        ]);

        $this->addForeignKey('fk_article_attachment_article', '{{%article_attachment}}', 'article_id', '{{%article}}', 'id', 'cascade', 'cascade');
        $this->addForeignKey('fk_article_author', '{{%article}}', 'author_id', '{{%user}}', 'id', 'cascade', 'cascade');
        $this->addForeignKey('fk_article_updater', '{{%article}}', 'updater_id', '{{%user}}', 'id', 'set null', 'cascade');
        $this->addForeignKey('fk_article_category', '{{%article}}', 'category_id', '{{%article_category}}', 'id');
        $this->addForeignKey('fk_article_category_section', '{{%article_category}}', 'parent_id', '{{%article_category}}', 'id', 'cascade', 'cascade');
        
        $this->createTable('{{%page}}', [
            'id' => $this->primaryKey(),
            'slug' => $this->string(2048)->notNull(),
            'title' => $this->string(512)->notNull(),
            'body' => $this->text()->notNull(),
            'view' => $this->string(),
            'status' => $this->smallInteger()->notNull(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);
        
        $this->createTable('{{%widget_menu}}', [
            'id' => $this->primaryKey(),
            'key' => $this->string(32)->notNull(),
            'title' => $this->string()->notNull(),
            'items' => $this->text()->notNull(),
            'status' => $this->smallInteger()->notNull()->defaultValue(0)
        ], $tableOptions);
        
        $this->createTable('{{%widget_text}}', [
            'id' => $this->primaryKey(),
            'key' => $this->string()->notNull(),
            'title' => $this->string()->notNull(),
            'body' => $this->text()->notNull(),
            'status' => $this->smallInteger(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);

        $this->createIndex('idx_widget_text_key', '{{%widget_text}}', 'key');
        
        $this->createTable('{{%widget_carousel}}', [
            'id' => $this->primaryKey(),
            'key' => $this->string()->notNull(),
            'status' => $this->smallInteger()->defaultValue(0)
        ], $tableOptions);

        $this->createTable('{{%widget_carousel_item}}', [
            'id' => $this->primaryKey(),
            'carousel_id' => $this->integer()->notNull(),
            'base_url'=>$this->string(1024),
            'path'=>$this->string(1024),
            'type'=>$this->string(),
            'url' => $this->string(1024),
            'caption' => $this->string(1024),
            'status' => $this->smallInteger()->notNull()->defaultValue(0),
            'order' => $this->integer()->defaultValue(0),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);

        $this->addForeignKey('fk_item_carousel', '{{%widget_carousel_item}}', 'carousel_id', '{{%widget_carousel}}', 'id', 'cascade', 'cascade');
        
        $this->createTable('{{%file_storage_item}}', [
            'id' => $this->primaryKey(),
            'component' => $this->string()->notNull(),
            'base_url' => $this->string(1024)->notNull(),
            'path' => $this->string(1024)->notNull(),
            'type' => $this->string(),
            'size' => $this->integer(),
            'name' => $this->string(),
            'upload_ip' => $this->string(15),
            'created_at' => $this->integer()->notNull()
        ], $tableOptions);
        
        Yii::$app->db->schema->refresh();
    }
}
