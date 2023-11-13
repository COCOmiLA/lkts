<?php

namespace common\modules\abiturient\traits\bachelor;

use common\models\dictionary\AdmissionCategory;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorTargetReception;
use common\modules\abiturient\models\bachelor\EducationData;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

trait BachelorApplicationDefaultSpecialityParamTrait
{
    

    






    public function getOnlyOneActiveQueryQuery(
        string $method,
        string $class,
        string $selectColumn
    ): ActiveQuery {

        $tn = $class::tableName();
        $subQuery = $this->{$method}()
            ->select(["{$tn}.{$selectColumn}"])
            ->groupBy("{$tn}.{$selectColumn}")
            ->having(['=', 'COUNT(*)', 1]);
        return $class::find()
            ->andWhere(['IN', "{$tn}.{$selectColumn}", $subQuery])
            ->andWhere(['archive' => 0]);
    }

    


    public function getOnlyOneEducationQuery(): ActiveQuery
    {
        $class = EducationData::class;

        return $this->getOnlyOneActiveQueryQuery('getEducations', $class, 'application_id');
    }

    


    public function getOnlyOneEducation(): ?EducationData
    {
        $onlyOneEducation = $this->getOnlyOneEducationQuery()->one();

        return $onlyOneEducation;
    }

    


    public function getOnlyOneTargetReceptionQuery(): ActiveQuery
    {
        $class = BachelorTargetReception::class;

        return $this->getOnlyOneActiveQueryQuery('getBachelorTargetReceptions', $class, 'id_application');
    }

    


    public function getOnlyOneTargetReceptionId()
    {
        $onlyOneTargetReception = $this->getOnlyOneTargetReceptionQuery()->one();

        return ArrayHelper::getValue($onlyOneTargetReception, 'id');
    }

    


    public function hasBachelorPreferencesSpecialRight(): bool
    {
        return $this->getBachelorPreferencesSpecialRight()->exists();
    }

    


    public function getBasisAdmissionCategoryId()
    {
        if (
            !Yii::$app->session->get('isAutofillSpecialty') &&
            $this->hasBachelorPreferencesSpecialRight()
        ) {
            return null;
        }

        $admissionCategory = AdmissionCategory::findByUID(Yii::$app->configurationManager->getCode('category_all'));

        return ArrayHelper::getValue($admissionCategory, 'id');
    }

    


    public function getSpecificLawAdmissionCategoryId()
    {
        $admissionCategory = AdmissionCategory::findByUID(
            Yii::$app->configurationManager->getCode('category_specific_law')
        );

        return ArrayHelper::getValue($admissionCategory, 'id');
    }
}
