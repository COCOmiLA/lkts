<?php


namespace common\models\traits;

use common\modules\abiturient\models\bachelor\AdmissionAgreement;
use common\modules\abiturient\models\bachelor\AgreementDecline;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use yii\db\ActiveQuery;
use yii\db\Expression;

trait BachelorApplicationAdmissionAgreementTrait
{
    







    public static function admissionAgreementQuery(ActiveQuery $query, bool $needExistAgreement = true): ActiveQuery
    {
        $tnAdmissionAgreement = AdmissionAgreement::tableName();
        $tnBachelorSpeciality = BachelorSpeciality::tableName();
        $tnBachelorApplication = BachelorApplication::tableName();

        $queryExpression = ['EXISTS'];
        if (!$needExistAgreement) {
            $queryExpression = ['NOT IN', "{$tnBachelorApplication}.id"];
        }
        $subQuerySelection = [new Expression('1')];
        if (!$needExistAgreement) {
            $subQuerySelection = ["{$tnBachelorSpeciality}.application_id"];
        }

        return $query->andWhere(array_merge(
            $queryExpression,
            [BachelorSpeciality::find()
                ->select($subQuerySelection)
                ->andWhere("{$tnBachelorApplication}.id = {$tnBachelorSpeciality}.application_id")
                ->andWhere([
                    'EXISTS',
                    AdmissionAgreement::find()
                        ->select([new Expression('1')])
                        ->andWhere("{$tnBachelorSpeciality}.id = {$tnAdmissionAgreement}.speciality_id")
                        ->andWhere(['!=', "{$tnAdmissionAgreement}.status", AdmissionAgreement::STATUS_MARKED_TO_DELETE])
                        ->active()
                ])
                ->active()]
        ));
    }

    






    public static function hasAdmissionAgreementQuery(ActiveQuery $query): ActiveQuery
    {
        return static::admissionAgreementQuery($query);
    }

    






    public static function doesNotHaveAdmissionAgreementQuery(ActiveQuery $query): ActiveQuery
    {
        return static::admissionAgreementQuery($query, false);
    }

    







    public static function agreementDeclineQuery(ActiveQuery $query, bool $needExistAgreementDecline = true): ActiveQuery
    {
        $tnAdmissionAgreement = AdmissionAgreement::tableName();
        $tnBachelorSpeciality = BachelorSpeciality::tableName();
        $tnBachelorApplication = BachelorApplication::tableName();
        $tnAdmissionAgreementDecline = AgreementDecline::tableName();

        $queryExpression = ['EXISTS'];
        if (!$needExistAgreementDecline) {
            $queryExpression = ['NOT IN', "{$tnBachelorApplication}.id"];
        }
        $subQuerySelection = [new Expression('1')];
        if (!$needExistAgreementDecline) {
            $subQuerySelection = ["{$tnBachelorSpeciality}.application_id"];
        }

        return $query->andWhere(array_merge(
            $queryExpression,
            [
                BachelorSpeciality::find()
                    ->select($subQuerySelection)
                    ->andWhere("{$tnBachelorApplication}.id = {$tnBachelorSpeciality}.application_id")
                    ->andWhere([
                        'EXISTS',
                        AdmissionAgreement::find()
                            ->select([new Expression('1')])
                            ->andWhere("{$tnBachelorSpeciality}.id = {$tnAdmissionAgreement}.speciality_id")
                            ->andWhere(["{$tnAdmissionAgreement}.status" => AdmissionAgreement::STATUS_MARKED_TO_DELETE])
                            ->andWhere([
                                'EXISTS',
                                AgreementDecline::find()
                                    ->select([new Expression('1')])
                                    ->andWhere("{$tnAdmissionAgreement}.id = {$tnAdmissionAgreementDecline}.agreement_id")
                                    ->active()
                            ])
                            ->active()
                    ])
                    ->active()
            ]
        ));
    }

    






    public static function hasAgreementDeclineQuery(ActiveQuery $query): ActiveQuery
    {
        return static::agreementDeclineQuery($query);
    }

    






    public static function doesNotHasAgreementDeclineQuery(ActiveQuery $query): ActiveQuery
    {
        return static::agreementDeclineQuery($query, false);
    }
}
