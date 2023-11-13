<?php

use backend\models\DictionaryUpdateHistory;
use common\components\Migration\MigrationWithDefaultOptions;




class m230314_094932_skip_dictionary_update extends MigrationWithDefaultOptions
{
    public function up()
    {
        try {
            
            DictionaryUpdateHistory::setUpdateTime('loadFakeDictionary', time() + 3600);
        } catch (Throwable $e) {
            echo "Не удалось избежать обновления справочников по причине: {$e->getMessage()}";
        }
    }
}
