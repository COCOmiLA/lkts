<?php

/**
 * @copyright Copyright (c) 2016 dianakaal
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author dianakaal <dianakaal@gmail.com>
 * @Date: 12/02/16
 * @Description: Combination of Yii framework's Input Mask and yii2-date-picker widget.
 * @package sguinfocom\DatePickerMaskedWidget
 */

namespace sguinfocom\DatePickerMaskedWidget;

use yii\bootstrap4\BootstrapPluginAsset;
use yii\web\AssetBundle;

/**
 * DatePickerMaskedWidgetAsset
 *
 */
class DatePickerMaskedWidgetAsset extends AssetBundle
{
    //$sourcePath: specifies the root directory that contains the asset files in this bundle. 
    public $sourcePath = '@bower';

    //$depends: an array listing the names of the asset bundles that this bundle depends on, in order. 
    public $depends = [BootstrapPluginAsset::class]; //IS THIS AUTOMATICALLY INCLUDED IN YII2? so I don't need to add it to my project separately

    public function init()
    {
        $this->css[] = YII_DEBUG ? 'bootstrap-datepicker/dist/css/bootstrap-datepicker3.standalone.css' : 'bootstrap-datepicker/dist/css/bootstrap-datepicker3.standalone.min.css';

        $this->js[] = YII_DEBUG ? 'bootstrap-datepicker/dist/js/bootstrap-datepicker.js' : 'bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js';
        $this->js[] = YII_DEBUG ? 'inputmask/dist/jquery.inputmask.bundle.js' : 'inputmask/dist/min/jquery.inputmask.bundle.min.js';
    }
}
