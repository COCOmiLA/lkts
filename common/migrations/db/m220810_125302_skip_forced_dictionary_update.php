<?php

use backend\models\DictionaryUpdateHistory;
use common\components\Migration\MigrationWithDefaultOptions;




class m220810_125302_skip_forced_dictionary_update extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        try {
            
            DictionaryUpdateHistory::setUpdateTime('loadFakeDictionary', time() + 3600);
        } catch (Throwable $e) {
            echo "Не удалось избежать обновления справочников по причине: {$e->getMessage()}";
        }
    }

}
