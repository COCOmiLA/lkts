<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\modules\abiturient\models\bachelor\AdmissionCampaign;




class m221122_085651_rename_column extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        if ($this->db->getTableSchema(AdmissionCampaign::tableName())->getColumn('count_target_specs_separately_regardless_of_multiprofile_competition')) {
            $this->renameColumn(AdmissionCampaign::tableName(), 'count_target_specs_separately_regardless_of_multiprofile_competition', 'count_target_specs_separately');
        }
    }
}
