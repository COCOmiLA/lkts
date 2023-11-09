<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\drafts\DraftsManager;
use common\modules\abiturient\models\IndividualAchievement;

class m220302_135210_restore_ia_to_apps extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        foreach (BachelorApplication::find()->joinWith(['user'])->with(['type.campaign'])->andWhere(['not', ['user.id' => null]])->all() as $app) {
            $user = $app->user;
            $campaign = \yii\helpers\ArrayHelper::getValue($app, 'type.campaign');
            if ($user && $campaign) {
                $app_ias = IndividualAchievement::find()
                    ->active()
                    ->andWhere([IndividualAchievement::tableName() . '.user_id' => $user->id])
                    ->joinWith('achievementType')
                    ->andWhere(['dictionary_individual_achievement.campaign_code' => $campaign->code])
                    ->andWhere([IndividualAchievement::tableName() . '.application_id' => null])
                    ->all();
                foreach ($app_ias as $app_ia) {
                    $clone = DraftsManager::makeCopy($app_ia);
                    $clone->application_id = $app->id;
                    $clone->save(false);
                }
            }
        }

        $old_ias = IndividualAchievement::find()
            ->active()
            ->andWhere(['application_id' => null])
            ->all();
        foreach ($old_ias as $old_ia) {
            $old_ia->delete();
        }
    }
}
