<?php

use backend\models\FiltersSetting;
use common\components\Migration\MigrationWithDefaultOptions;
use common\modules\abiturient\models\bachelor\AdmissionAgreement;
use common\modules\abiturient\models\bachelor\AgreementDecline;
use common\modules\abiturient\models\bachelor\BachelorApplication;




class m210715_041028_rename_sended_at_columns extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->renameColumn(BachelorApplication::tableName(), 'sended_at', 'sent_at');
        $this->renameColumn(AdmissionAgreement::tableName(), 'sended_at', 'sent_at');
        $this->renameColumn(AgreementDecline::tableName(), 'sended_at', 'sent_at');
        Yii::$app->db->schema->refresh();
        $filter_sended_at = FiltersSetting::findOne(['name' => 'sended_at']);
        if ($filter_sended_at) {
            $filter_sended_at->name = 'sent_at';
            $filter_sended_at->save(false, ['name']);
        }
    }

    


    public function safeDown()
    {
        $filter_sended_at = FiltersSetting::findOne(['name' => 'sent_at']);
        if ($filter_sended_at) {
            $filter_sended_at->name = 'sended_at';
            $filter_sended_at->save(false, ['name']);
        }

        $this->renameColumn(BachelorApplication::tableName(), 'sent_at', 'sended_at');
        $this->renameColumn(AdmissionAgreement::tableName(), 'sent_at', 'sended_at');
        $this->renameColumn(AgreementDecline::tableName(), 'sent_at', 'sended_at');
        Yii::$app->db->schema->refresh();

    }

}
