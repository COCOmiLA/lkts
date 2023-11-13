<?php

namespace common\models\relation_presenters\comparison\interfaces;

interface IComparisonResult
{
    public function getResult();

    public function getLeftEntity();

    public function getRightEntity();

    public function getDifferences();

    public function setCompareRelated(bool $compareRelated);
}