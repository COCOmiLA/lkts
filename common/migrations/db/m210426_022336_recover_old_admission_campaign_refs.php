<?php

use common\components\AppUpdate;
use common\components\Migration\MigrationWithDefaultOptions;
use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;




class m210426_022336_recover_old_admission_campaign_refs extends MigrationWithDefaultOptions
{
    private $appUpdate;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->appUpdate = new AppUpdate();
    }


    


    public function safeUp()
    {
        if (Yii::$app->db->schema->getTableSchema(StoredAdmissionCampaignReferenceType::tableName()) === null || !StoredAdmissionCampaignReferenceType::find()->exists()) {
            return true;
        }
        $this->appUpdate->updateReferenceTable(StoredAdmissionCampaignReferenceType::class);
    }

}
