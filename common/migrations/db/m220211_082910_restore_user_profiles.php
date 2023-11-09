<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m220211_082910_restore_user_profiles extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        
        $profiles = \common\models\UserProfile::find()->all();
        foreach ($profiles as $profile) {
            
            if ($profile->locale != 'ru') {
                $profile->locale = 'ru';
                $profile->save(false);
            }
        }
    }
}
