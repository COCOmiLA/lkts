<?php

namespace common\modules\student\components\grade;

use common\models\EmptyCheck;
use common\models\User;
use Yii;
use yii\base\Component;

class GradeLoader extends Component implements \common\modules\student\interfaces\DynamicComponentInterface,
    \common\modules\student\interfaces\RoutableComponentInterface
{
    public $user_guid;
    public $componentName = "Успеваемость";
    public $baseRoute = 'student/grade';

    public function getComponentName()
    {
        return $this->componentName;
    }

    public function getBaseRoute()
    {
        return $this->baseRoute;
    }

    public function isAllowedToRole($role)
    {
        switch ($role) {
            case (User::ROLE_STUDENT):
                return true;
            default:
                return false;
        }
    }

    public static function getConfig()
    {
        return [
            'class' => self::class,
        ];
    }

    public static function getController()
    {
        return __NAMESPACE__ . '\\controllers\\GradeController';
    }

    public static function getUrlRules()
    {
        return [
            'student/grade' => 'grade/index',
        ];
    }


    public function setParams(string $user_guid)
    {
        $this->user_guid = $user_guid;
    }

    private function loadRecordBooks(): array
    {
        return Yii::$app->getPortfolioService->loadRawRecordbooks($this->user_guid);
    }

    public function loadGrades()
    {
        $recordbooks = $this->loadRecordBooks();
        $responseData = [];
        foreach ($recordbooks as $recordbook) {
            $response = Yii::$app->soapClientStudent->load("GetEducationalPerformance",
                [
                    'UserId' => $this->user_guid,
                    'RecordbookId' => $recordbook->RecordbookId
                ]
            );

            if ($response === false) {
                continue;
            }

            $data = [];

            if (isset($response->return->MarkRecord) && $response->return->MarkRecord != null) {
                if (!is_array($response->return->MarkRecord)) {
                    $response->return->MarkRecord = [$response->return->MarkRecord];
                }
                foreach ($response->return->MarkRecord as $mark_record) {
                    if (isset($mark_record->MarkRecord)) {
                        array_push($data, $mark_record->MarkRecord);
                    } else {
                        array_push($data, $mark_record);
                    }
                }

                $only_parents = array_values(array_filter($data, function ($item) {
                    return !isset($item->BlockSubject);
                }));
                $only_children = array_values(array_filter($data, function ($item) {
                    return isset($item->BlockSubject);
                }));

                $responseData[$recordbook->SpecialtyName] = array_values(array_map(function ($parent) use ($only_children) {
                    $parent->Children = $this->getChildsByParentSubject($only_children, $parent->Subject, $parent->Term);
                    return $parent;
                }, $only_parents));
            }
        }

        return $responseData;
    }

    public function getChildsByParentSubject($children_array, $parent_subject, $parent_term)
    {
        $found_childs = [];
        foreach ($children_array as $possible_child) {
            if ($parent_term == $possible_child->Term && isset($possible_child->BlockSubject) && $parent_subject->ReferenceUID == $possible_child->BlockSubject->ReferenceUID) {
                $found_childs[] = $possible_child;
            }
        }
        $filtered_children_array = array_values(array_filter($children_array, function ($item) use ($parent_subject) {
            return (isset($parent_subject->ReferenceUID) && isset($item->Subject) && isset($item->Subject->ReferenceUID) && $item->Subject->ReferenceUID !== $parent_subject->ReferenceUID);
        }));
        return array_values(array_map(function ($potential_parent) use ($filtered_children_array) {
            $potential_parent->Children = $this->getChildsByParentSubject($filtered_children_array, $potential_parent->Subject, $potential_parent->Term);
            return $potential_parent;
        }, $found_childs));
    }
}

