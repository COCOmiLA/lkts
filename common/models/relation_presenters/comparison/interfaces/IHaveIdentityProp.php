<?php

namespace common\models\relation_presenters\comparison\interfaces;

interface IHaveIdentityProp
{
    public function getIdentityString(): string;
}