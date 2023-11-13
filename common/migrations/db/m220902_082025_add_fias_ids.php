<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\dictionary\Fias;
use common\models\dictionary\FiasDoma;




class m220902_082025_add_fias_ids extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        $this->addColumn(FiasDoma::tableName(), 'fias_id', $this->string(36)->null()->comment('Идентификатор записи ФИАС'));
        $this->addColumn(FiasDoma::tableName(), 'building_fias_id', $this->string(36)->null()->comment('Идентификатор записи ФИАС из регистра доп структур'));
        $this->addColumn(Fias::tableName(), 'fias_id', $this->string(36)->null()->comment('Идентификатор записи ФИАС'));
        $this->addColumn(Fias::tableName(), 'parent_fias_id', $this->string(36)->null()->comment('Идентификатор записи ФИАС'));

        $this->createIndex('doma_fias_id', FiasDoma::tableName(), 'fias_id');
        $this->createIndex('fias_id', Fias::tableName(), 'fias_id', true);
        try {
            $this->dropIndex('kladr_code', Fias::tableName());
        } catch (\Throwable $e) {
        }
        $this->createIndex('kladr_code', Fias::tableName(), 'code');
    }

    


    public function down()
    {
        $this->dropIndex('doma_fias_id', FiasDoma::tableName());
        $this->dropIndex('fias_id', Fias::tableName());
        $this->dropIndex('kladr_code', Fias::tableName());

        $this->dropColumn(FiasDoma::tableName(), 'building_fias_id');
        $this->dropColumn(FiasDoma::tableName(), 'fias_id');
        $this->dropColumn(Fias::tableName(), 'fias_id');
        $this->dropColumn(Fias::tableName(), 'parent_fias_id');

    }
}
