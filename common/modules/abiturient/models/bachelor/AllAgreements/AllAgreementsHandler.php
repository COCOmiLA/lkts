<?php

namespace common\modules\abiturient\models\bachelor\AllAgreements;

use common\models\errors\RecordNotValid;
use common\models\ToAssocCaster;
use common\modules\abiturient\models\bachelor\AdmissionAgreement;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use stdClass;
use yii\base\InvalidArgumentException;
use yii\helpers\ArrayHelper;

class AllAgreementsHandler
{
    




    public static function ProcessAllAgreements(BachelorApplication $application, $raw_all_agreements)
    {
        $raw_all_agreements = ToAssocCaster::getAssoc($raw_all_agreements);
        if (empty($raw_all_agreements)) {
            $raw_all_agreements = [];
        }
        if (!is_array($raw_all_agreements) || ArrayHelper::isAssociative($raw_all_agreements)) {
            $raw_all_agreements = [$raw_all_agreements];
        }

        $touched_local_ids = [];
        foreach ($raw_all_agreements as $raw_all_agreement) {
            $date = strtotime($raw_all_agreement['Date']);
            $local_agreement = $application
                ->getAgreementRecords()
                ->andWhere(['speciality_guid' => $raw_all_agreement['ApplicationStringGUID']])
                ->andWhere(['type' => $raw_all_agreement['AgreementType']])
                ->andWhere(['date' => $date])
                ->one();
            if (!$local_agreement) {
                $local_agreement = new AgreementRecord();
                $local_agreement->application_id = $application->id;
                $local_agreement->speciality_guid = $raw_all_agreement['ApplicationStringGUID'];
                $local_agreement->speciality_name = $raw_all_agreement['ApplicationDescription'];
                $local_agreement->type = $raw_all_agreement['AgreementType'];
                $local_agreement->date = $date;
                if (!$local_agreement->save()) {
                    throw new RecordNotValid($local_agreement);
                }
            }
            $touched_local_ids[] = $local_agreement->id;
        }

        $to_delete = $application->getAgreementRecords()->andWhere(['not', ['id' => $touched_local_ids]])->all();
        foreach ($to_delete as $to_delete_agreement) {
            $to_delete_agreement->delete();
        }
    }

    public static function UpdateConsentDates(BachelorApplication $application)
    {
        foreach ($application->rawSpecialities as $speciality) {
            $latest_agreement_info = $speciality->getAgreementRecords()
                ->orderBy([AgreementRecord::tableName() . '.date' => SORT_DESC])
                ->one();
            if ($latest_agreement_info) {
                switch ($latest_agreement_info->type) {
                    case AgreementRecord::AGREEMENT_TYPE_WITHDRAW:
                        $decline = ArrayHelper::getValue($speciality, 'rawAgreementDecline');
                        if ($decline && (int)$decline->sent_at < $latest_agreement_info->date) {
                            $decline->sent_at = $latest_agreement_info->date;
                            $decline->save(false, ['sent_at']);
                        }
                        break;
                    case AgreementRecord::AGREEMENT_TYPE_AGREED:
                        $agreement = $speciality
                            ->getAnyAgreements()
                            ->andWhere(['!=', 'admission_agreement.status', AdmissionAgreement::STATUS_MARKED_TO_DELETE])
                            ->one();
                        if ($agreement && (int)$agreement->sent_at < $latest_agreement_info->date) {
                            $agreement->sent_at = $latest_agreement_info->date;
                            $agreement->save(false, ['sent_at']);
                        }
                        break;
                    default:
                        throw new InvalidArgumentException();
                }
            }
        }
    }

    public static function MaxConsentDate(BachelorApplication $application): int
    {
        $max_date = 0;
        
        $latest_agreement_info = $application->getAgreementRecords()
            ->orderBy([AgreementRecord::tableName() . '.date' => SORT_DESC])
            ->one();
        if ($latest_agreement_info) {
            $max_date = max($max_date, (int)$latest_agreement_info->date);
        }
        return $max_date;
    }
}
