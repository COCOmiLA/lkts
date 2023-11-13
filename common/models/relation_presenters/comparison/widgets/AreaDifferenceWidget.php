<?php

namespace common\models\relation_presenters\comparison\widgets;

use common\assets\TooltipAsset;
use common\models\relation_presenters\comparison\ComparisonHelper;
use common\models\relation_presenters\comparison\interfaces\IComparisonResult;

class AreaDifferenceWidget extends \yii\base\Widget
{
    


    public $comparison_entry;
    public $property_path;
    public $wrap_in_flexbox = false;

    public function init()
    {
        parent::init();
        
        if (!is_array($this->comparison_entry)) {
            $this->comparison_entry = [
                ComparisonHelper::DEFAULT_DIFFERENCE_CLASS => $this->comparison_entry
            ];
        }
        ob_start();

    }

    public function run()
    {
        $content = ob_get_clean();

        [$difference, $difference_class] = (new ComparisonHelper($this->comparison_entry, $this->property_path))->getRenderedDifference();

        $view = $this->getView();
        TooltipAsset::register($view);

        return $this->render('@common/models/relation_presenters/comparison/widgets/views/area_difference', [
            'content' => $content,
            'difference' => $difference,
            'difference_class' => $difference_class,
            'wrap_in_flexbox' => $this->wrap_in_flexbox,
        ]);
    }
}