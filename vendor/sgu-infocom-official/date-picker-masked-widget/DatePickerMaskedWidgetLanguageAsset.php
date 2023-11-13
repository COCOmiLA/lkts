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

use yii\web\AssetBundle;

/**
 * DatePickerMaskedWidgetLanguageAsset
 *
 */
class DatePickerMaskedWidgetLanguageAsset extends AssetBundle
{
    public $sourcePath = '@bower/bootstrap-datepicker/dist/locales';

    public $depends = [DateRangePickerMaskedWidgetAsset::class];
}
