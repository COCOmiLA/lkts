<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m211208_143532_add_archive_column_to_bachelor_egeresult extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn(
            'bachelor_egeresult',
            'archive',
            $this->boolean()->defaultValue(false)
        );
        $this->addColumn(
            'bachelor_egeresult',
            'archived_at',
            $this->integer()->defaultValue(null)
        );
    }

    


    public function safeDown()
    {
        $this->dropColumn(
            'bachelor_egeresult',
            'archive'
        );
        $this->dropColumn(
            'bachelor_egeresult',
            'archived_at'
        );
    }
}
