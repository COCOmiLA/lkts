<?php

namespace common\components\AdmissionCampaignDictionaryManager;


use common\components\ReferenceTypeManager\exceptions\ReferenceManagerCannotSerializeDataException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerValidationException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerWrongReferenceTypeClassException;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\components\soapException;
use common\models\DebuggingSoap;
use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;
use common\modules\abiturient\models\bachelor\AdmissionCampaign;

class AdmissionCampaignDictionaryManager
{
    








    public static function FindAdmissionCampaign($xmlData): ?AdmissionCampaign
    {

        $admission_campaign = null;

        if (!empty($xmlData->CampaignRef)) {
            $reference = ReferenceTypeManager::GetOrCreateReference(StoredAdmissionCampaignReferenceType::class, $xmlData->CampaignRef);
            $admission_campaign = AdmissionCampaign::findOne([
                'ref_id' => $reference->id,
            ]);
        } else {
            $admission_campaign = AdmissionCampaign::findOne([
                'code' => (string)$xmlData->IdPK,
            ]);
        }

        return $admission_campaign;
    }

    



    public static function FetchAdmissionCampaign()
    {
        return \Yii::$app->soapClientAbit->load('GetPK', [], DebuggingSoap::getInstance()->isLoggingForDictionarySoapEnabled);
    }
}