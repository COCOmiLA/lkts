<?php

namespace common\modules\student\models;

use stdClass;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;







class Teacher extends Model
{
    
    private const ATTRIBUTE_LIST = [
        ReferenceEmployer::class => 'EmployerRef',
        ReferencePosition::class => 'PositionRef',
    ];

    public $EmployerRef = null;

    public $PositionRef = null;

    function __construct($config = [])
    {
        if (isset($config['rawTeacher'])) {
            $this->constructByRawData($config['rawTeacher']);

            unset($config['rawTeacher']);
        }
        if (isset($config['guid'])) {
            if (!isset($config['departmentSerialNumber'])) {
                $config['departmentSerialNumber'] = 0;
            }
            $this->constructByGuid(
                $config['guid'],
                $config['departmentSerialNumber']
            );

            unset($config['guid']);
            unset($config['departmentSerialNumber']);
        }
        parent::__construct($config);
    }

    




    private function constructByRawData(stdClass $rawTeacher): void
    {
        foreach (Teacher::ATTRIBUTE_LIST as $class => $attributeName) {
            if (isset($rawTeacher->{$attributeName})) {
                $this->{$attributeName} = new $class(['rawReference' => $rawTeacher->{$attributeName}]);
            }
        }
    }

    





    private function constructByGuid(string $guid, int $departmentSerialNumber = 0): void
    {
        $employerStates = $this->getEmployerStates($guid);
        if (!$employerStates) {
            return;
        }

        foreach (Teacher::ATTRIBUTE_LIST as $class => $attributeName) {
            $reference = ArrayHelper::getValue($employerStates, "{$departmentSerialNumber}.{$attributeName}");
            if ($reference) {
                $this->{$attributeName} = new $class(['rawReference' => $reference]);
            }
        }
    }

    




    private function getEmployerStates(string $guid): array
    {
        $rawUser = Yii::$app->getPortfolioService->loadReference(
            [
                'Parameter' => $guid,
                'ParameterType' => 'Код',
                'ParameterRef' => 'Справочник.ФизическиеЛица'
            ]
        );

        $states = Yii::$app->getPortfolioService->loadEmployerStates(['PersonRef' => $rawUser->return->Reference]);
        $result = [];
        if (isset($states)) {
            if (!is_array($states->return->EmployerState)) {
                $states->return->EmployerState = [$states->return->EmployerState];
            }

            $result = $states->return->EmployerState;
        }

        return $result;
    }

    


    public function renderTeacher(): string
    {
        $render = '';
        $employer = trim(ArrayHelper::getValue(
            $this,
            'EmployerRef.ReferenceName'
        ));
        $position = trim(ArrayHelper::getValue(
            $this,
            'PositionRef.ReferenceName'
        ));
        if ($employer) {
            $render = $employer;
        }
        if ($position) {
            if ($render) {
                $render .= " ({$position})";
            } else {
                $render = $employer;
            }
        }

        return $render;
    }

    


    public function buildFor1C(): stdClass
    {
        $result = [];

        foreach (Teacher::ATTRIBUTE_LIST as $attributeName) {
            $result[$attributeName] = $this->{$attributeName}->buildFor1C();
        }

        return (object)$result;
    }
}
