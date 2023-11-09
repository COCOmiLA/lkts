<?php

namespace common\models\traits;


trait HasDirtyAttributesTrait
{
    public function hasChangedAttributes(): bool
    {
        if (method_exists($this, 'beforeCheckChangedAttributes')) {
            $this->beforeCheckChangedAttributes();
        }
        if ($this->getIsNewRecord()) {
            return true;
        }
        $attrs_to_check = $this->getDirtyAttributes();
        $old_attrs = $this->getOldAttributes();
        foreach ($attrs_to_check as $attr_name => $new_attr_value) {
            $prepared_to_compare_old_value = $old_attrs[$attr_name];
            $prepared_to_compare_new_value = $new_attr_value;

            if ($prepared_to_compare_old_value != $prepared_to_compare_new_value) {
                return true;
            }
        }
        return false;
    }
}