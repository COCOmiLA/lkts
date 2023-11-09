<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\Attachment;




class m220812_072331_unlink_regulation_attachments_from_apps_and_ques_directly extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        
        $query = Attachment::find()
            ->select([Attachment::tableName() . '.id'])
            ->joinWith(['userRegulation ur'])
            ->andWhere(['not', ['ur.id' => null]]);
        Attachment::updateAll(['application_id' => null, 'questionary_id' => null], ['id' => $query->column()]);
    }

}
