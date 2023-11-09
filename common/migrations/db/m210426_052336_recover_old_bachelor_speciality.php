<?php

use common\components\dictionaryManager\dictionaryManager;
use common\components\Migration\MigrationWithDefaultOptions;
use common\models\dictionary\Speciality;





class m210426_052336_recover_old_bachelor_speciality extends MigrationWithDefaultOptions
{
    private $dictionaryManager;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->dictionaryManager = new dictionaryManager();
    }

    


    public function safeUp()
    {
        if (Yii::$app->db->schema->getTableSchema(Speciality::tableName()) === null || !Speciality::find()->exists()) {
            return true;
        }
        $this->dictionaryManager->loadSpecialities();
        return true;
    }

}
