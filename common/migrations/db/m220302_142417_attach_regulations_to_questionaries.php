<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\UserRegulation;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\drafts\DraftsManager;




class m220302_142417_attach_regulations_to_questionaries extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        
        $user_regulations = UserRegulation::find()->andWhere(['abiturient_questionary_id' => null])->all();
        foreach ($user_regulations as $user_regulation) {
            $user = $user_regulation->owner;
            $questionaries = AbiturientQuestionary::find()->where(['user_id' => $user->id])->all();
            foreach ($questionaries as $questionary) {
                $user_regulation_to_link = $user_regulation;
                if ($user_regulation_to_link->abiturient_questionary_id) {
                    $user_regulation_to_link = DraftsManager::makeCopy($user_regulation_to_link);
                }
                $user_regulation_to_link->abiturient_questionary_id = $questionary->id;
                $user_regulation_to_link->save(false);
            }
        }
    }
}
