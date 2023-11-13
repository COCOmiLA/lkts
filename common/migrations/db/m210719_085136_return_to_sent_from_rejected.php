<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\modules\abiturient\models\bachelor\BachelorApplication;




class m210719_085136_return_to_sent_from_rejected extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        BachelorApplication::updateAll([
            'status' => BachelorApplication::STATUS_SENT,
            'moderator_comment' => 'Заявление возвращено к рассмотрению.'
        ], [
            'status' => BachelorApplication::STATUS_NOT_APPROVED,
            'moderator_comment' => 'Ваше заявление устарело и было актуализировано из приемной кампании'
        ]);
    }

}
