<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\drafts\ApplicationAndQuestionaryLinker;
use common\modules\abiturient\models\interfaces\IDraftable;

class m220302_142439_link_questionary_and_apps extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        Yii::$app->configurationManager->suspendUnspecifiedCodesError(true);
        $needs_link = BachelorApplication::find()
            ->joinWith(['linkedAbiturientQuestionary q'])
            ->andWhere([BachelorApplication::tableName() . '.draft_status' => [IDraftable::DRAFT_STATUS_SENT, IDraftable::DRAFT_STATUS_MODERATING]])
            ->andWhere(['q.id' => null])
            ->all();
        foreach ($needs_link as $app) {
            

            ApplicationAndQuestionaryLinker::setUpQuestionaryLink($app);
        }
    }
}
