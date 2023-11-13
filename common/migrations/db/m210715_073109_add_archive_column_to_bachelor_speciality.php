<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\modules\abiturient\models\bachelor\AdmissionAgreement;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;




class m210715_073109_add_archive_column_to_bachelor_speciality extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn(BachelorSpeciality::tableName(), 'archive', $this->boolean()->defaultValue(false));
        $this->addColumn(AdmissionAgreement::tableName(), 'archive', $this->boolean()->defaultValue(false));
        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropColumn(BachelorSpeciality::tableName(), 'archive');
        $this->dropColumn(AdmissionAgreement::tableName(), 'archive');
        Yii::$app->db->schema->refresh();
    }


}
