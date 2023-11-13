<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210512_090632_alter_doc_type_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->alterColumn(\common\models\dictionary\DocumentType::tableName(), 'formula', $this->string(1000));
    }

    


    public function safeDown()
    {
        $this->alterColumn(\common\models\dictionary\DocumentType::tableName(), 'formula', $this->string(1000)->notNull());
    }
}
