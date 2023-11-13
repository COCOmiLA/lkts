<?php

use backend\models\DictionaryUpdateHistory;
use common\components\Migration\MigrationWithDefaultOptions;




class m220728_133132_add_fake_dictionary_update extends MigrationWithDefaultOptions
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
