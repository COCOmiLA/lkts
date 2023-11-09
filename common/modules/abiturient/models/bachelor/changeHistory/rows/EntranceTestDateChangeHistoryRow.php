<?php

namespace common\modules\abiturient\models\bachelor\changeHistory\rows;

use common\models\dictionary\DictionaryDateTimeOfExamsSchedule;
use common\modules\abiturient\models\bachelor\BachelorDatePassingEntranceTest;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryEntityClass;
use Yii;

class EntranceTestDateChangeHistoryRow extends DefaultChangeHistoryRow
{
    protected function getAttributeName(ChangeHistoryEntityClass $class, string $attribute, $value): string
    {
        if ($attribute === 'date_time_of_exams_schedule_id') {
            $date = DictionaryDateTimeOfExamsSchedule::findOne($value);
            if ($date && $date->eventTypeRef) {
                return BachelorDatePassingEntranceTest::humanizer($date->eventTypeRef->reference_name);
            }
            return Yii::t(
                'abiturient/change-history-widget',
                'Название поля отображающего запись на дату сдачи вступительного испытания: `Вступительное испытание`'
            );
        }
        return parent::getAttributeName($class, $attribute, $value);
    }

    protected function prettifyValue(string $attribute, $value)
    {
        if ($attribute === 'date_time_of_exams_schedule_id') {
            $date = DictionaryDateTimeOfExamsSchedule::findOne($value);
            if ($date) {
                return BachelorDatePassingEntranceTest::generateName($date->attributes);
            }
        }

        return parent::prettifyValue($attribute, $value);
    }
}
