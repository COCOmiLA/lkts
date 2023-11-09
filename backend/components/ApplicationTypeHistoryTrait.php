<?php

namespace backend\components;

use backend\models\applicationTypeHistory\ApplicationTypeHistory;
use common\modules\abiturient\models\bachelor\ApplicationType;
use Yii;
use yii\db\ActiveQuery;

trait ApplicationTypeHistoryTrait
{
    


    public function getApplicationTypeId()
    {
        if ($this instanceof ApplicationType) {
            return $this->id;
        }
        return $this->application_type_id;
    }

    


    public function getApplicationTypeHistories()
    {
        return $this->hasMany(ApplicationTypeHistory::class, ['application_type_id' => 'id']);
    }

    


    public function hasApplicationTypeHistories()
    {
        return $this->getApplicationTypeHistories()->exists();
    }

    




    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($this->isNewRecord || $this->getApplicationTypeId() === null) {
            return true;
        }

        $user = Yii::$app->user->identity;
        if (!$user) {
            return false;
        }

        $attributesToCompere = $this->getAttributesToCompere();
        $oldAttributesToCompere = $this->getOldAttributesToCompere();
        $newSettingValueList = array_diff_assoc($attributesToCompere, $oldAttributesToCompere);
        $oldSettingValueList = array_diff_assoc($oldAttributesToCompere, $attributesToCompere);

        if (!$newSettingValueList && !$oldSettingValueList) {
            return true;
        }

        $listChanges = [];
        foreach ($newSettingValueList as $attr => $newSettingValue) {
            if (in_array($attr, ApplicationType::NOT_BOOLEAN_FIELDS)) {
                continue;
            }

            if (!key_exists($attr, $oldSettingValueList)) {
                continue;
            }
            $oldSettingValue = $oldSettingValueList[$attr];

            $listChanges[] = [
                'attr' => $attr,
                'newValue' => $newSettingValue,
                'oldValue' => $oldSettingValue,
            ];
        }

        return ApplicationTypeHistory::createNewEntry(
            $user,
            ApplicationTypeHistory::CHANGE_BOOLEAN_SETTINGS_ATTRIBUTE,
            $this->getApplicationTypeId(),
            ApplicationType::class,
            $listChanges
        );
    }

    private function getListToCompere(string $functionName)
    {
        $attributes = $this->{$functionName}();
        if ($this instanceof ApplicationType) {
            return $attributes;
        }

        $list = [$attributes['name'] => $attributes['value']];

        return $list;
    }

    public function getAttributesToCompere()
    {
        return $this->getListToCompere('getAttributes');
    }

    public function getOldAttributesToCompere()
    {
        return $this->getListToCompere('getOldAttributes');
    }
}
