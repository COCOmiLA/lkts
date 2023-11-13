<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\modules\abiturient\models\bachelor\CampaignInfo;




class m200923_085943_resolve_campaign_info_changes extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $infos = CampaignInfo::find()->active()->all();
        $specs = \common\models\dictionary\AdmissionCategory::find()->active()->all();
        foreach ($infos as $info) {
            if($info->finance_code === Yii::$app->configurationManager->getCode('target_reception_code') || $info->finance_code === Yii::$app->configurationManager->getCode('full_cost_recovery_code')){
                $newInfo = new CampaignInfo();
                $newInfo->attributes = $info->attributes;
                $newInfo->category_code = Yii::$app->configurationManager->getCode('category_all');
                if($newInfo->validate())  {
                    $newInfo->save(false);
                } else {
                    Yii::error($newInfo->errors, 'ERROR_RESOLVING_CAMPAIGN_INFO');
                    return false;
                }
            } else {
                if(isset($specs) && $specs) {
                    foreach ($specs as $spec) {
                        $newInfo = new CampaignInfo();
                        $newInfo->attributes = $info->attributes;
                        $newInfo->category_code = $spec->code;
                        if($newInfo->validate())  {
                            $newInfo->save();
                        } else {
                            Yii::error($newInfo->errors, 'ERROR_RESOLVING_CAMPAIGN_INFO');
                            return false;
                        }
                    }
                }
            }
            $info->archive = true;
            $info->save();
        }
    }

    


    public function safeDown()
    {
        return;
    }

    













}
