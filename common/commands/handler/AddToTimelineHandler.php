<?php

namespace common\commands\handler;

use common\commands\command\AddToTimelineCommand;
use common\models\TimelineEvent;
use trntv\tactician\base\BaseHandler;
use Yii;




class AddToTimelineHandler extends BaseHandler
{
    



    public function handle($command)
    {
        $model = new TimelineEvent();
        $model->application = Yii::$app->id;
        $model->category = $command->category;
        $model->event = $command->event;
        $model->data = json_encode($command->data, JSON_UNESCAPED_UNICODE);
        return $model->save(false);
    }
}
