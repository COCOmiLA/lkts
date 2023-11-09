<?php

namespace common\models\relation_presenters\comparison\interfaces;

interface ICanGivePropsToCompare
{
    public function getPropsToCompare(): array;
}