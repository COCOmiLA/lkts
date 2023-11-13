<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221227_073531_remove_building_fias_id_column extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        $columnData = $this->getDb()->getSchema()->getTableSchema('{{%dictionary_fias_doma}}')->getColumn('building_fias_id');
        if ($columnData) {
            $this->dropColumn('{{%dictionary_fias_doma}}', 'building_fias_id');
        }
        try {
            $this->dropIndex('idx_fias_doma_fias_id_name', '{{%dictionary_fias_doma}}');
        } catch (Throwable $e) {
            echo 'Не удалось удалить индекс idx_fias_doma_fias_id_name';
        }
        $type = $this->string(1000);
        
        if ($this->db->driverName === 'pgsql') {
            $type = $this->text();
        }
        $columnData = $this->getDb()->getSchema()->getTableSchema('{{%dictionary_fias_doma}}')->getColumn('name');
        if ($columnData) {
            $this->alterColumn('{{%dictionary_fias_doma}}', 'name', $type);
        }
    }

    


    public function down()
    {
        $this->addColumn('{{%dictionary_fias_doma}}', 'building_fias_id', $this->string(36)->null()->comment('Идентификатор дома в ФИАС'));
        $this->alterColumn('{{%dictionary_fias_doma}}', 'name', $this->string(255));
        $this->createIndex('idx_fias_doma_fias_id_name', '{{%dictionary_fias_doma}}', ['fias_id', 'name']);
    }
}
