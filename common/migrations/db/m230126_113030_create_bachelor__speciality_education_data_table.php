<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\components\ReferenceTypeManager\traits\migrations\createDropReferenceTable;




class m230126_113030_create_bachelor__speciality_education_data_table extends MigrationWithDefaultOptions
{
    use createDropReferenceTable;

    private const TN = '{{%bachelor_speciality_education_data}}';

    private const LIST = [
        'education_data_id' => '{{%education_data}}',
        'bachelor_speciality_id' => '{{%bachelor_speciality}}',
    ];

    


    public function safeUp()
    {
        $this->createTable(self::TN, [
            'id' => $this->primaryKey(),
            'bachelor_speciality_id' => $this->integer()->notNull(),
            'education_data_id' => $this->integer()->notNull(),
        ]);

        $normalTablename = $this->normalizeTablename(self::TN);
        foreach (self::LIST as $column => $refTable) {
            $this->createIndex(
                "IDX-{$normalTablename}-{$column}",
                self::TN,
                $column
            );
            $this->addForeignKey(
                "FK-{$normalTablename}-{$column}",
                self::TN,
                $column,
                $refTable,
                'id',
                'NO ACTION'
            );
        }

        $this->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $normalTablename = $this->normalizeTablename(self::TN);
        foreach (self::LIST as $column => $refTable) {
            $this->dropForeignKey("FK-{$normalTablename}-{$column}", self::TN);
            $this->dropIndex("IDX-{$normalTablename}-{$column}", self::TN);
        }

        $this->dropTable(self::TN);

        $this->db->schema->refresh();
    }
}
