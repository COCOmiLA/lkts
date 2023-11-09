<?php

namespace common\components;

use stdClass;
use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;

class getPortfolioService extends Component
{
    public function loadRawRecordbooks(string $guid, bool $overwrite = false)
    {
        $recordbooks = Yii::$app->session->get('Recordbooks', []);
        if (!ArrayHelper::isAssociative($recordbooks)) {
            $recordbooks = []; 
        }
        if (!isset($recordbooks[$guid]) || $overwrite) {
            $response = Yii::$app->soapClientStudent->load('GetRecordbooks', ['UserId' => $guid]);
            if (isset($response->return->Error) && $response->return->Error != null) {
                Yii::error("Error code: {$response->return->Error->Code} description: {$response->return->Error->Description}");
                $recordbooks[$guid] = [];
            }
            if (isset($response->return->Recordbook) && $response->return->Recordbook != null) {
                $found_recordbooks = $response->return->Recordbook;
                if (!is_array($found_recordbooks)) {
                    $found_recordbooks = [$found_recordbooks];
                }
                $recordbooks[$guid] = $found_recordbooks;
            }
            Yii::$app->session->set('Recordbooks', $recordbooks);
        }

        return $recordbooks[$guid] ?? [];
    }

    public function loadRecordbooks(string $guid)
    {
        $recordbooks = $this->loadRawRecordbooks($guid);
        $formatted_recordbooks = [];
        for ($i = 0; $i < count($recordbooks); $i++) {
            $tmp = clone $recordbooks[$i];
            if (strpos($tmp->CurriculumName, 'Зачетная книжка №') === false) {
                $tmp->CurriculumName = "Зачетная книжка №{$tmp->RecordbookName}. {$tmp->CurriculumName}";
            }
            $formatted_recordbooks[] = $tmp;
        }
        return $formatted_recordbooks;
    }

    public function loadPlanTree($recordbook_id, $type, $properties)
    {
        if ($recordbook_id != null) {
            $formattedData = [];
            $response = Yii::$app->soapClientStudent->load("GetPlanTree", [
                'OwnerId' => $recordbook_id,
                'OwnerType' => $type,
                'OwnerProperties' => $properties
            ]);
            if (isset($response->return->Error)) {
                Yii::$app->session->addFlash('warning', $response->return->Error->Description, true);
                Yii::error("Error code: " . $response->return->Error->Code . " description: " . $response->return->Error->Description);
                return null;
            }
            if ($response === false) {
                return null;
            }
            $planTrees = [];
            if (!empty($response->return) && isset($response->return->PlanTreeStrings)) {
                $planTrees = $response->return->PlanTreeStrings;
                if (!is_array($planTrees)) {
                    $planTrees = [$planTrees];
                }
                foreach ($planTrees as $planTree) {
                    $formattedData[] = Yii::$app->treeLoader->loadTree($planTree);
                }
            }
            return $formattedData;
        } else {
            return null;
        }
    }

    public function loadLapResults($PlanUID, $LapUID)
    {
        if ($PlanUID != null && $LapUID != null) {
            $response = Yii::$app->soapClientStudent->load("GetLapResults", [
                'PlanUID' => $PlanUID,
                'LapUID' => $LapUID
            ]);
            if (isset($response->return->Error)) {
                Yii::$app->session->addFlash('warning', $response->return->Error->Description, true);
                Yii::error("Error code: " . $response->return->Error->Code . " description: " . $response->return->Error->Description);
                return null;
            }
            if ($response === false) {
                return null;
            }
            return $response;
        } else {
            return null;
        }
    }

    public function loadLapResultClasses($PlanUID, $LapUID)
    {
        if ($PlanUID != null && $LapUID != null) {
            $response = Yii::$app->soapClientStudent->load("GetLapResultClasses", [
                'PlanUID' => $PlanUID,
                'LapUID' => $LapUID
            ]);
            if (isset($response->return->Error)) {
                Yii::$app->session->addFlash('warning', $response->return->Error->Description, true);
                Yii::error("Error code: " . $response->return->Error->Code . " description: " . $response->return->Error->Description);
                return null;
            }
            if ($response === false) {
                return null;
            }
            return $response;
        } else {
            return null;
        }
    }


    public function loadLapResultClassesProperties($PlanUID, $LapUID, $LapResultClassUID)
    {
        if ($PlanUID != null && $LapUID != null) {
            $response = Yii::$app->soapClientStudent->load("GetLapResultClassProperties", [
                'PlanUID' => $PlanUID,
                'LapUID' => $LapUID,
                'LapResultClassUID' => $LapResultClassUID
            ]);
            if (isset($response->return->Error) && empty($response->return->LapResultStrings)) {
                Yii::$app->session->addFlash('warning', $response->return->Error->Description, true);
                Yii::error("Error code: " . $response->return->Error->Code . " description: " . $response->return->Error->Description);
                return null;
            }
            if ($response === false) {
                return null;
            }
            return $response;
        } else {
            return null;
        }
    }


    public function loadAttachedFileList($RefUID, $RefClassName)
    {
        if ($RefUID != null && $RefClassName != null) {
            $response = Yii::$app->soapClientStudent->load("GetAttachedFileList", [
                'ResultRef' => [
                    'ReferenceName' => '',
                    'ReferenceId' => '',
                    'ReferenceUID' => $RefUID,
                    'ReferenceClassName' => $RefClassName
                ]
            ]);
            if (isset($response->return->Error)) {
                Yii::$app->session->addFlash('warning', $response->return->Error->Description, true);
                Yii::error("Error code: " . $response->return->Error->Code . " description: " . $response->return->Error->Description);
                return null;
            }
            if ($response === false) {
                return null;
            }

            return $response;
        } else {
            return null;
        }
    }

    private function processResponse(?stdClass $response): ?stdClass
    {
        if ($response === false) {
            return null;
        }
        if (isset($response->return->Error)) {
            Yii::$app->session->addFlash('warning', $response->return->Error->Description, true);
            Yii::error("Error code: " . $response->return->Error->Code . " description: " . $response->return->Error->Description);
            return null;
        }

        return $response;
    }

    public function saveLapResult($data)
    {
        $response = Yii::$app->soapClientStudent->load(
            "SaveLapResult",
            $data
        );
        return $this->processResponse($response);
    }

    public function loadCommentaries($data)
    {
        $response = Yii::$app->soapClientStudent->load("GetCommentaries", $data);
        return $this->processResponse($response);
    }

    
    public function saveAttachedFile($data)
    {
        $response = Yii::$app->soapClientStudent->load(
            "SaveAttachedFile",
            $data
        );
        return $this->processResponse($response);
    }

    public function deleteAttachedFile($data)
    {
        $response = Yii::$app->soapClientStudent->load(
            "DeleteAttachedFile",
            $data
        );
        return $this->processResponse($response);
    }


    public function deleteLapResult($data)
    {
        $response = Yii::$app->soapClientStudent->load(
            "DeleteLapResult",
            $data
        );
        return $this->processResponse($response);
    }


    public function loadAllowedRatingSystems($data)
    {
        $response = Yii::$app->soapClientStudent->load(
            "GetAllowedRatingSystems",
            $data
        );
        return $this->processResponse($response);
    }


    public function loadAllowedMarks($data)
    {
        $response = Yii::$app->soapClientStudent->load(
            "GetAllowedMarks",
            $data
        );
        return $this->processResponse($response);
    }

    public function loadBinaryData($data)
    {
        $response = Yii::$app->soapClientStudent->load(
            "GetBinaryData",
            $data
        );
        return $this->processResponse($response);
    }

    public function loadEmployerStates($data)
    {
        $response = Yii::$app->soapClientStudent->load(
            "GetEmployerStates",
            $data
        );
        return $this->processResponse($response);
    }

    public function loadEmployerUMK($data)
    {
        $response = Yii::$app->soapClientStudent->load(
            "GetEmployerUMK",
            $data
        );
        return $this->processResponse($response);
    }

    public function loadEmployersStudents($data)
    {
        $response = Yii::$app->soapClientStudent->load(
            "GetEmployersStudents",
            $data
        );
        return $this->processResponse($response);
    }

    public function loadEmployersCurriculums($data)
    {
        $response = Yii::$app->soapClientStudent->load(
            "GetEmployersCurriculums",
            $data
        );
        return $this->processResponse($response);
    }

    public function saveMark($data)
    {
        $response = Yii::$app->soapClientStudent->load(
            "SaveMark",
            $data
        );
        return $this->processResponse($response);
    }

    public function saveCommentary($data)
    {
        $response = Yii::$app->soapClientStudent->load(
            "CreateCommentary",
            $data
        );
        return $this->processResponse($response);
    }

    public function loadDisciplines($plan_id, $semester_id)
    {
        $response = Yii::$app->soapClientStudent->load(
            "GetCurriculumLoad",
            [
                'CurriculumId' => $plan_id,
                'TermId' => $semester_id,
            ]
        );
        return $this->processResponse($response);
    }

    public function loadReference($data)
    {
        $response = Yii::$app->soapClientStudent->load(
            "GetReference",
            $data
        );
        return $this->processResponse($response);
    }

    public function loadReferences($data)
    {
        $response = Yii::$app->soapClientStudent->load(
            "GetReferences",
            $data
        );
        return $this->processResponse($response);
    }
}
