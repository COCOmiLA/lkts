<?php

namespace common\models\relation_presenters\comparison\results;

use common\models\relation_presenters\comparison\EntitiesComparator;
use common\models\relation_presenters\comparison\interfaces\IComparisonResult;

class FieldComparisonResult extends \yii\base\BaseObject implements IComparisonResult
{
    public $result = null;
    public $old;
    public $new;
    public $prop;
    protected $_compare_related = true;

    public function __construct($old, $new, $prop)
    {
        parent::__construct();
        $this->old = $old;
        $this->new = $new;
        $this->prop = $prop;
    }

    public function setCompareRelated(bool $compareRelated)
    {
        $this->_compare_related = $compareRelated;
        return $this;
    }

    public function getResult(): bool
    {
        if (!is_null($this->result)) {
            return $this->result;
        }
        if (is_null($this->old)) {
            
            $this->result = boolval($this->new);
        } else {
            $old_value = EntitiesComparator::getPropertyValue($this->old, $this->prop);
            $new_value = EntitiesComparator::getPropertyValue($this->new, $this->prop);
            $this->result = trim((string)$old_value) != trim((string)$new_value);
        }

        return $this->result;
    }

    public function getLeftEntity()
    {
        return $this->old;
    }

    public function getRightEntity()
    {
        return $this->new;
    }

    public function getDifferences(): array
    {
        $result = [];
        if ($this->getResult()) {
            $entity = $this->old;
            if (!$entity) {
                $entity = $this->new;
            }
            $result[$entity->getAttributeLabel($this->prop)] = EntitiesComparator::getPropertyValue($this->old, $this->prop);
        }
        return $result;
    }

}