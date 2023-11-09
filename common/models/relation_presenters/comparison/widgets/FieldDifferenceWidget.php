<?php

namespace common\models\relation_presenters\comparison\widgets;

use common\assets\TooltipAsset;
use common\models\relation_presenters\comparison\ComparisonHelper;
use common\models\relation_presenters\comparison\interfaces\IComparisonResult;
use yii\widgets\ActiveField;

class FieldDifferenceWidget extends \yii\base\Widget
{
    


    public $comparison_entry;
    public $property_path;
    


    public $field;

    public function init()
    {
        parent::init();
        
        if (!is_array($this->comparison_entry)) {
            $this->comparison_entry = [
                ComparisonHelper::DEFAULT_DIFFERENCE_CLASS => $this->comparison_entry
            ];
        }
    }

    public function run()
    {
        $helper = (new ComparisonHelper($this->comparison_entry, $this->property_path));
        [$difference, $difference_class] = $helper->getRenderedDifference();

        $view = $this->getView();
        TooltipAsset::register($view);

        return $this->render('@common/models/relation_presenters/comparison/widgets/views/field_difference', [
            'field' => $this->field,
            'difference' => $difference,
            'difference_class' => $difference_class,
        ]);
    }
}