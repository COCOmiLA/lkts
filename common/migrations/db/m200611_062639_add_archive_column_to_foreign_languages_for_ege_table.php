<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200611_062639_add_archive_column_to_foreign_languages_for_ege_table extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        $this->addColumn('foreign_languages_for_ege', 'archive', $this->boolean());
    }

    


    public function down()
    {
        $this->dropColumn('foreign_languages_for_ege', 'archive');
    }
}
