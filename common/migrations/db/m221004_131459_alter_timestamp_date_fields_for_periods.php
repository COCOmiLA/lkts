<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221004_131459_alter_timestamp_date_fields_for_periods extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->renameColumn('{{%periods_to_send_agreement}}', 'start', 'old_start');
        $this->renameColumn('{{%periods_to_send_agreement}}', 'end', 'old_end');
        $this->addColumn('{{%periods_to_send_agreement}}', 'start', $this->string(100));
        $this->addColumn('{{%periods_to_send_agreement}}', 'end', $this->string(100));
        $this->db->schema->refresh();
        $infos = \common\modules\abiturient\models\bachelor\PeriodToSendAgreement::find()->each();
        foreach ($infos as $info) {
            $info->start = date('Y-m-d H:i:s', $info->old_start);
            $info->end = date('Y-m-d H:i:s', $info->old_end);
            $info->save(false);
        }
        $this->dropColumn('{{%periods_to_send_agreement}}', 'old_start');
        $this->dropColumn('{{%periods_to_send_agreement}}', 'old_end');
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%periods_to_send_agreement}}', 'start');
        $this->dropColumn('{{%periods_to_send_agreement}}', 'end');
        $this->addColumn('{{%periods_to_send_agreement}}', 'start', $this->integer());
        $this->addColumn('{{%periods_to_send_agreement}}', 'end', $this->integer());
    }
}
