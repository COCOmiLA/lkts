<?php


namespace common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS;

use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\traits\BaseApplicationPackageBuilderTrait;
use common\components\EntrantTestManager\EntrantTestManager;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\EmptyCheck;
use common\models\ToAssocCaster;
use common\modules\abiturient\models\bachelor\BachelorResultCentralizedTesting;
use common\modules\abiturient\models\bachelor\EgeResult;
use yii\helpers\ArrayHelper;

class EntranceTestsResultsFullPackageBuilder extends BaseApplicationPackageBuilder
{
    public function build()
    {
        $disciplines = [];
        $hasCorrectCitizenship = BachelorResultCentralizedTesting::hasCorrectCitizenship($this->application);

        foreach ($this->application->egeResults as $result) {
            

            $dis = [
                'SubjectRef' => $result->getDisciplineRef(), 
                'EntranceTestTypeRef' => ReferenceTypeManager::GetReference($result->cgetExamForm),
                'ParentSubjectRef' => ReferenceTypeManager::getEmptyRefTypeArray(),
            ];
            if ($result->hasChildren()) {
                $dis['ParentSubjectRef'] = ReferenceTypeManager::GetReference($result->cgetDiscipline);
            }
            $dis['LanguageRef'] = ReferenceTypeManager::GetReference($result, 'language');
            $dis['SpecialRequirementsRefs'] = ReferenceTypeManager::GetReference($result, 'specialRequirement');
            if (!EmptyCheck::isEmpty(ArrayHelper::getValue($result, 'discipline_points'))) {
                $dis['Mark'] = ArrayHelper::getValue($result, 'discipline_points');
            }
            $dis['EgeYear'] = $result->egeyear ?? null;

            $dis['Approved'] = $result->status == EgeResult::STATUS_VERIFIED ? 1 : 0;

            $dis['ReadOnly'] = $result->readonly ?? null;

            if ($result->isExam()) {
                if ($hasCorrectCitizenship && $result->bachelorResultCentralizedTesting) {
                    $dis['Recalculation'] = (new CentralizedTestingFullPackageBuilder($this->application, $result))->build();
                }

                $dis['ExamsSchedule'] = (new ExamsScheduleFullPackageBuilder($this->application, $result))->build();

                $reasonForExam = $result->reasonForExam;
                if (!empty($reasonForExam)) {
                    $dis['AdditionalElement'] = [
                        EntranceTestsResultsFullPackageBuilder::buildAdditionalAttribute('Reason', $reasonForExam->code)
                    ];
                }
            }

            $disciplines[] = $dis;
        }

        return $disciplines;
    }

    public function update($raw_data): bool
    {
        $raw_data = ToAssocCaster::getAssoc($raw_data);
        if (empty($raw_data)) {
            $raw_data = [];
        }
        if (!is_array($raw_data) || ArrayHelper::isAssociative($raw_data)) {
            $raw_data = [$raw_data];
        }

        return EntrantTestManager::proceedEntrantTestFrom1C($this->application, $raw_data);
    }
}
