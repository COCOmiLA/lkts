<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\components\ReferenceTypeManager\traits\migrations\createDropReferenceTable;




class m230228_061659_change_FK_delete_action extends MigrationWithDefaultOptions
{
    use createDropReferenceTable;

    private const TN = '{{%bachelor_speciality_education_data}}';

    private const LIST = [
        'education_data_id' => '{{%education_data}}',
        'bachelor_speciality_id' => '{{%bachelor_speciality}}',
    ];

    


    public function safeUp()
    {
        $this->changeFK('CASCADE');
    }

    


    public function safeDown()
    {
        $this->changeFK('NO ACTION');
    }

    public function changeFK(string $deleteAction): void
    {
        $normalTablename = $this->normalizeTablename(self::TN);
        foreach (self::LIST as $column => $refTable) {
            $this->dropForeignKey("FK-{$normalTablename}-{$column}", self::TN);
            $this->addForeignKey(
                "FK-{$normalTablename}-{$column}",
                self::TN,
                $column,
                $refTable,
                'id',
                $deleteAction
            );
        }

        $this->db->schema->refresh();
    }
}
