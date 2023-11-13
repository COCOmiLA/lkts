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

use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;
use yii\web\JsExpression;
use yii\web\view;

class DatePickerMaskedWidget extends InputWidget
{
    /************************************ DatePicker variables ***************************/

    use DatePickerMaskedWidgetTrait;

    /**
     * @var string the addon markup if you wish to display the input as a component. If you don't wish to render as a
     *
     * component then set it to null or false.
     */
    public $addon = '<i class="fa fa-calendar"></i>';
    /**
     * @var string the template to render the input.
     */
    public $template = '{input}{addon}';
    /**
     * @var bool whether to render the input as an inline calendar
     */
    public $inline = false;

    /************************************ MaskedInput variables ***************************/

    /**
     * The name of the jQuery plugin to use for the MaskedInput widget.
     */
    const PLUGIN_NAME = 'inputmask';
    /**
     * @var string|array|JsExpression the input mask (e.g. '99/99/9999' for date input). The following characters
     *
     * can be used in the mask and are predefined:
     *
     * - `a`: represents an alpha character (A-Z, a-z)
     * - `9`: represents a numeric character (0-9)
     * - `*`: represents an alphanumeric character (A-Z, a-z, 0-9)
     * - `[` and `]`: anything entered between the square brackets is considered optional user input. This is
     *   based on the `optionalmarker` setting in [[clientOptions]].
     *
     * Additional definitions can be set through the [[definitions]] property.
     */
    public $mask;
    /**
     * @var array custom mask definitions to use. Should be configured as `maskSymbol => settings`, where
     *
     * - `maskSymbol` is a string, containing a character to identify your mask definition and
     * - `settings` is an array, consisting of the following entries:
     *   - `validator`: string, a JS regular expression or a JS function.
     *   - `cardinality`: int, specifies how many characters are represented and validated for the definition.
     *   - `prevalidator`: array, validate the characters before the definition cardinality is reached.
     *   - `definitionSymbol`: string, allows shifting values from other definitions, with this `definitionSymbol`.
     */
    public $definitions;
    /**
     * @var array custom aliases to use. Should be configured as `maskAlias => settings`, where
     *
     * - `maskAlias` is a string containing a text to identify your mask alias definition (e.g. 'phone') and
     * - `settings` is an array containing settings for the mask symbol, exactly similar to parameters as passed in [[clientOptions]].
     */
    public $aliases; //have not figured out how to use that, it's not used on InputMask demo at all
    /**
     * @var array the JQuery plugin options for the input mask plugin.
     *
     * @see https://github.com/RobinHerbots/jquery.inputmask
     */
    public $maskOptions = [];
    /**
     * @var array the HTML attributes for the input tag.
     *
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = ['class' => 'form-control'];
    /**
     * @var string the type of the input tag. Currently only 'text' and 'tel' are supported.
     *
     * @see https://github.com/RobinHerbots/jquery.inputmask
     * @since 2.0.6
     */
    public $type = 'text';

    /**
     * @var string the hashed variable to store the pluginOptions
     */
    protected $_hashVar;

    /************************************** end of variables ********************************/

    /**
     * @inheritdoc
     *
     * init() - should contain the widget properties
     */
    public function init()
    {
        parent::init();

        if ($this->inline) {
            $this->options['readonly'] = 'readonly';
            Html::addCssClass($this->options, 'text-center');
        }
        if ($this->size) {
            Html::addCssClass($this->options, 'input-' . $this->size);
            Html::addCssClass($this->containerOptions, 'input-group-' . $this->size);
        }
        Html::addCssClass($this->options, 'form-control');
        Html::addCssClass($this->containerOptions, 'input-group date');
    }

    /**
     * @inheritdoc
     *
     * run()  - should contain rendering result of the widget
     */
    public function run()
    {
        $input = $this->hasModel()
            ? Html::activeTextInput($this->model, $this->attribute, $this->options)
            : Html::textInput($this->name, $this->value, $this->options);

        if ($this->inline) {
            $input .= '<div></div>';
        }
        if ($this->addon && !$this->inline) {
            $addon = Html::tag('span', $this->addon, ['class' => 'input-group-prepend input-group-append input-group-text']);
            $addon = Html::tag('div', $addon, ['class' => 'input-group-append']);

            $input = strtr($this->template, ['{input}' => $input, '{addon}' => $addon]);
            $input = Html::tag('div', $input, $this->containerOptions);
        }
        if ($this->inline) {
            $input = strtr($this->template, ['{input}' => $input, '{addon}' => '']);
        }
        echo $input;

        $this->registerClientScript();
    }

    /**
     * Generates a hashed variable to store the plugin 'clientOptions' and `maskOptions`.
     * Helps in reusing the variable for similar options passed for other widgets on the same page.
     * The following special data attribute will also be added to the input field to allow
     * accessing the client options via javascript:     *
     * - 'data-plugin-inputmask' will store the hashed variable storing the plugin options.
     *
     * @param View $view the view instance
     *
     * @author [Thiago Talma](https://github.com/thiagotalma)
     */
    protected function hashPluginOptions($view)
    {

        /* MaskedInput */
        $encOptions = empty($this->maskOptions) ? '{}' : Json::htmlEncode($this->maskOptions);
        $this->_hashVar = self::PLUGIN_NAME . '_' . hash('crc32', $encOptions);
        $this->options['data-plugin-' . self::PLUGIN_NAME] = $this->_hashVar;
        $view->registerJs("var {$this->_hashVar} = {$encOptions};\n", View::POS_HEAD);
    }

    /**
     * Initializes mask options
     */
    protected function initMaskOptions()
    {
        $options = $this->maskOptions;
        foreach ($options as $key => $value) {
            if (
                !$value instanceof JsExpression && in_array($key, [
                    'oncomplete', 'onincomplete', 'oncleared', 'onKeyUp',
                    'onKeyDown', 'onBeforeMask', 'onBeforePaste', 'onUnMask', 'isComplete', 'determineActiveMasksetIndex'
                ])
            ) {
                $options[$key] = new JsExpression($value);
            }
        }
        $this->maskOptions = $options;
    }


    /**
     * Registers required script for the plugin to work as DatePicker
     */
    public function  registerClientScript()
    {
        $js_datepicker = [];
        $view = $this->getView();

        // @codeCoverageIgnoreStart
        if ($this->language !== null) {
            $this->clientOptions['language'] = $this->language;
            DatePickerMaskedWidgetLanguageAsset::register($view)->js[] = 'bootstrap-datepicker.' . $this->language . '.min.js';
        } else {
            DatePickerMaskedWidgetAsset::register($view);
        }
        // @codeCoverageIgnoreEnd

        $id = $this->options['id']; //the id of the element where to place the datepicker & inputmask
        $selector = ";jQuery('#$id')";

        if ($this->addon || $this->inline) {
            $selector .= ".parent()";
        }

        $options = !empty($this->clientOptions) ? Json::encode($this->clientOptions) : '';

        if ($this->inline) {
            $this->clientEvents['changeDate'] = "function (e){ jQuery('#$id').val(e.format());}";
        }

        $js_datepicker[] = "$selector.datepicker($options);";

        if (!empty($this->clientEvents)) {
            foreach ($this->clientEvents as $event => $handler) {
                $js_datepicker[] = "$selector.on('$event', $handler);";
            }
        }
        /* end of DatePicker registration */

        /*********************** maskedInput *************************/
        $js_mask = '';
        $view = $this->getView();
        $this->initMaskOptions(); //MaskedInput

        if (!empty($this->mask)) {
            $this->maskOptions['mask'] = $this->mask;
        }
        $this->hashPluginOptions($view);
        if (is_array($this->definitions) && !empty($this->definitions)) {
            $js_mask .= '$.extend($.' . self::PLUGIN_NAME . '.defaults.definitions, ' . Json::htmlEncode($this->definitions) . ");\n";
        }
        if (is_array($this->aliases) && !empty($this->aliases)) {
            $js_mask .= '$.extend($.' . self::PLUGIN_NAME . '.defaults.aliases, ' . Json::htmlEncode($this->aliases) . ");\n";
        }
        $id2 = $this->options['id'];
        $js_mask .= '$("#' . $id2 . '").' . self::PLUGIN_NAME . "(" . $this->_hashVar . ");\n";
        /* end of maskedInput code*/

        /*********** end result for both **********/
        $js_datepicker = implode("\n", $js_datepicker);
        $js_result = $js_datepicker . $js_mask;
        $view->registerJS($js_result);
    }
}
