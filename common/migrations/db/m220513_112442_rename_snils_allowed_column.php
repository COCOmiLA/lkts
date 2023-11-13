<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\modules\abiturient\models\bachelor\AdmissionCampaign;




class m220513_112442_rename_snils_allowed_column extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->renameColumn(AdmissionCampaign::tableName(), 'snils_allowed', 'snils_is_required');
    }

    


    public function safeDown()
    {
        $this->renameColumn(AdmissionCampaign::tableName(), 'snils_is_required', 'snils_allowed');
    }
}
