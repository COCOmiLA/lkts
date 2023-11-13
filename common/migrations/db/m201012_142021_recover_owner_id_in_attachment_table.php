<?php

use common\components\ini\iniSet;
use common\components\Migration\MigrationWithDefaultOptions;
use common\models\Attachment;




class m201012_142021_recover_owner_id_in_attachment_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        iniSet::disableTimeLimit();
        $batchSize = 1000;
        $attachments = Attachment::find()->with(['abiturientQuestionary', 'bachelorApplication']);

        foreach ($attachments->each($batchSize) as $attachment) {
            if (isset($attachment->abiturientQuestionary)) {
                $attachment->owner_id = $attachment->abiturientQuestionary->user_id;

                if ($attachment->validate(['owner_id'])) {
                    $attachment->save(false);
                }
            } elseif (isset($attachment->bachelorApplication)) {
                $attachment->owner_id = $attachment->bachelorApplication->user_id;

                if ($attachment->validate(['owner_id'])) {
                    $attachment->save(false);
                }
            }
        }
    }

    


    public function safeDown()
    {
        return;
    }

    













}
