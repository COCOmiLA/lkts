<?php

namespace common\modules\student\components\stipend\models;

class Stipend
{
    private const EMPTY_DATE = '0001-01-01T00:00:00';

    public $orderNumber;
    public $orderDate;
    public $orderRef;
    public $orderTypeRef;
    public $protocolNumber;
    public $protocolDate;
    public $protocolRef;
    public $calculationRef;
    public $paymentAmount;
    public $startDate;
    public $endDate;
    public $formOfEducationRef;
    public $facultyRef;
    public $specialtyRef;
    public $courseRef;
    public $studyGroupRef;
    public $subgroupRef;
    public $commissionDecisionRef;
    public $causeRef;

    public static function withData($data)
    {
        $instance = new self();

        foreach ($data as $key => $value) {
            $instance->$key = $value;
        }

        return $instance;
    }

    




    private function renderDate($date = '')
    {
        if ($date == Stipend::EMPTY_DATE) {
            return '-';
        }

        return date('d.m.Y', strtotime($date));
    }

    


    public function renderOrderDate()
    {
        return $this->renderDate($this->orderDate);
    }

    


    public function renderDateInterval()
    {
        $endDate = $this->renderDate($this->endDate);
        $startDate = $this->renderDate($this->startDate);

        if ($startDate != '-' && $endDate != '-') {
            return "{$startDate} - {$endDate}";
        }
        if ($startDate != '-' && $endDate == '-') {
            return $startDate;
        }
        if ($startDate == '-' && $endDate != '-') {
            return $endDate;
        }

        return '-';
    }
}
