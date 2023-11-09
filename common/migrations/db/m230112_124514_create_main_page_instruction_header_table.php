<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\components\ReferenceTypeManager\traits\migrations\createDropReferenceTable;




class m230112_124514_create_main_page_instruction_header_table extends MigrationWithDefaultOptions
{
    use createDropReferenceTable;

    private const TN = '{{%main_page_instruction_header}}';
    private const MAIN_PAGE_SETTING_TN = '{{%main_page_setting}}';

    


    public function safeUp()
    {
        if ($this->db->getTableSchema(self::TN, true) !== null) {
            return;
        }

        $this->createTable(
            self::TN,
            [
                'id' => $this->primaryKey(),

                'main_page_setting_id' => $this->integer()->notNull(),
                'header' => $this->string()->defaultValue(''),

                'created_at' => $this->integer(),
                'updated_at' => $this->integer(),
            ]
        );

        $this->createIndex(
            'IDX-' . self::normalizeTablename(self::TN) . '-to-mps',
            self::TN,
            ['main_page_setting_id']
        );
        $this->addForeignKey(
            'FK-' . self::normalizeTablename(self::TN) . '-to-mps',
            self::TN,
            ['main_page_setting_id'],
            self::MAIN_PAGE_SETTING_TN,
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

        $this->dropForeignKey('FK-' . self::normalizeTablename(self::TN) . '-to-mps', self::TN);
        $this->dropIndex('IDX-' . self::normalizeTablename(self::TN) . '-to-mps', self::TN);

        $this->dropTable(self::TN);

        Yii::$app->db->schema->refresh();
    }
}
