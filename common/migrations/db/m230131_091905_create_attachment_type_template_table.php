<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\components\ReferenceTypeManager\traits\migrations\createDropReferenceTable;




class m230131_091905_create_attachment_type_template_table extends MigrationWithDefaultOptions
{
    use createDropReferenceTable;

    private const TN = '{{%attachment_type_template}}';
    private const USER_TN = '{{%user}}';
    private const FILES_TN = '{{%files}}';
    private const ATTACHMENT_TYPE_TN = '{{%attachment_type}}';


    


    public function safeUp()
    {
        if ($this->db->getTableSchema(self::TN, true) !== null) {
            return;
        }

        $this->createTable(self::TN, [
            'id' => $this->primaryKey(),

            'file_id' => $this->integer()->defaultValue(null),
            'user_id' => $this->integer()->defaultValue(null),
            'attachment_type_id' => $this->integer()->defaultValue(null),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->createIndex(
            'IDX-' . self::normalizeTablename(self::TN) . '-to-files',
            self::TN,
            ['file_id']
        );
        $this->addForeignKey(
            'FK-' . self::normalizeTablename(self::TN) . '-to-files',
            self::TN,
            ['file_id'],
            self::FILES_TN,
            'id',
            'CASCADE'
        );

        $this->createIndex(
            'IDX-' . self::normalizeTablename(self::TN) . '-to-user',
            self::TN,
            ['user_id']
        );
        $this->addForeignKey(
            'FK-' . self::normalizeTablename(self::TN) . '-to-user',
            self::TN,
            ['user_id'],
            self::USER_TN,
            'id',
            'CASCADE'
        );

        $this->createIndex(
            'IDX-' . self::normalizeTablename(self::TN) . '-to-attachment_type',
            self::TN,
            ['attachment_type_id']
        );
        $this->addForeignKey(
            'FK-' . self::normalizeTablename(self::TN) . '-to-attachment_type',
            self::TN,
            ['attachment_type_id'],
            self::ATTACHMENT_TYPE_TN,
            'id',
            'CASCADE'
        );

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        if ($this->db->getTableSchema(self::TN, true) === null) {
            return;
        }

        $this->dropForeignKey('FK-' . self::normalizeTablename(self::TN) . '-to-attachment_type', self::TN);
        $this->dropIndex('IDX-' . self::normalizeTablename(self::TN) . '-to-attachment_type', self::TN);
        $this->dropForeignKey('FK-' . self::normalizeTablename(self::TN) . '-to-files', self::TN);
        $this->dropForeignKey('FK-' . self::normalizeTablename(self::TN) . '-to-user', self::TN);
        $this->dropIndex('IDX-' . self::normalizeTablename(self::TN) . '-to-files', self::TN);
        $this->dropIndex('IDX-' . self::normalizeTablename(self::TN) . '-to-user', self::TN);

        $this->dropTable(self::TN);

        Yii::$app->db->schema->refresh();
    }
}
