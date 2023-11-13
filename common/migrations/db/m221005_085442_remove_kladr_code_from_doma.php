<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221005_085442_remove_kladr_code_from_doma extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        
        $this->createTable('{{%kladr_codes}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(255)->notNull(),
        ]);
        
        $this->createIndex(
            'idx-kladr_codes-code',
            '{{%kladr_codes}}',
            'code'
        );
        $this->addColumn('{{%dictionary_fias_doma}}', 'code_id', $this->integer()->defaultValue(null));
        $this->createIndex(
            'idx-dictionary_fias_doma-code_id',
            '{{%dictionary_fias_doma}}',
            'code_id'
        );
        
        if ($this->db->getTableSchema('{{%dictionary_fias_doma}}')->getColumn('code') !== null) {
            
            $this->execute('INSERT INTO kladr_codes (code) SELECT DISTINCT code FROM dictionary_fias_doma WHERE code IS NOT NULL');
            
            $this->execute('UPDATE dictionary_fias_doma SET code_id = (SELECT id FROM kladr_codes WHERE kladr_codes.code = dictionary_fias_doma.code)');
            
            $this->dropColumn('{{%dictionary_fias_doma}}', 'code');
        }
    }

    


    public function safeDown()
    {
        
        if ($this->db->getTableSchema('{{%dictionary_fias_doma}}')->getColumn('code_id') !== null) {
            $this->dropColumn('{{%dictionary_fias_doma}}', 'code_id');
        }
        if ($this->db->getTableSchema('{{%dictionary_fias_doma}}')->getColumn('code') === null) {
            $this->addColumn('{{%dictionary_fias_doma}}', 'code', $this->string(255)->defaultValue(null));
        }
        
        if ($this->db->getTableSchema('{{%kladr_codes}}') !== null) {
            $this->dropTable('{{%kladr_codes}}');
        }
    }
}
