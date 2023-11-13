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

/**
 * DatePickerMaskedWidgetTrait holds common attributes
 */
/*
 * Traits are a mechanism for code reuse in single inheritance languages such as PHP.
 * A Trait is intended to reduce some limitations of single inheritance by enabling a developer
 * to reuse sets of methods freely in several independent classes living in different class hierarchies.
 * The semantics of the combination of Traits and classes is defined in a way which reduces complexity,
 * and avoids the typical problems associated with multiple inheritance and Mixins.
 * A Trait is similar to a class, but only intended to group functionality in a fine-grained and consistent way.
 * It is not possible to instantiate a Trait on its own. It is an addition to traditional inheritance and
 * enables horizontal composition of behavior; that is, the application of class members without requiring inheritance.
 */

trait DatePickerMaskedWidgetTrait
{
    /**
     * @var string the language to use
     */
    public $language;
    /**
     * @var array the options for the Bootstrap DatePicker plugin.
     * Please refer to the Bootstrap DatePicker plugin Web page for possible options.
     * @see http://bootstrap-datepicker.readthedocs.org/en/release/options.html
     */
    public $clientOptions = [];
    /**
     * @var array the event handlers for the underlying Bootstrap DatePicker plugin.
     * Please refer to the [DatePicker](http://bootstrap-datepicker.readthedocs.org/en/release/events.html) plugin
     * Web page for possible events.
     */
    public $clientEvents = [];
    /**
     * @var string the size of the input ('lg', 'md', 'sm', 'xs')
     */
    public $size;
    /**
     * @var array HTML attributes to render on the container
     */
    public $containerOptions = [];
}