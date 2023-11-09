<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\modules\abiturient\models\bachelor\AdmissionAgreementToDelete;




class m200819_134337_add_archive_column_to_agreement_to_delete_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%admission_agreement_to_delete}}', 'archive', $this->boolean()->null());
        Yii::$app->db->schema->refresh();
        AdmissionAgreementToDelete::updateAll(['archive' => false]);
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%agreement_to_delete}}', 'archive');
    }
}
