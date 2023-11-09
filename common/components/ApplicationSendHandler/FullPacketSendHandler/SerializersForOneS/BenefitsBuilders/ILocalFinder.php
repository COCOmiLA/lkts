<?php


namespace common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\BenefitsBuilders;


use yii\db\ActiveRecord;

interface ILocalFinder
{
    public function findLocalByRaw($raw_record, $excluded_ids = []): ?ActiveRecord;
}