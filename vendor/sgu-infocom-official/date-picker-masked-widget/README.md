DatePickerMaskedWidget for Yii2
========================================================================

A combination between the bootstrap datepicker and the input mask.
In other words you can have a date picker as well as an input mask simultaneously in the same field.


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require sguinfocom/date-picker-masked-widget="*"
```
or add

```json
"sguinfocom/date-picker-masked-widget": "*"
```

to the require section of your application's `composer.json` file.


USAGE
-----


#### Plain Date Picker

------> WITH A MODEL <------

**as a plain widget**
```
<?php
use sguinfocom\DatePickerMaskedWidget\DatePickerMaskedWidget;
?>
<?=
    DatePickerMaskedWidget::widget([
            'model' => '$modelName',
            'value' => '30-16-2016'
            'attribute' => false,
            'template' => '{addon}{input}',
            'language' => 'fi',
            'clientOptions' => [
            'autoclose' => true,
            'clearBtn' => true,
            'format' => 'dd.mm.yyyy',
            'todayBtn' => 'linked',
            'todayHighlight' => 'true',
            'weekStart' => '1',
            'calendarWeeks' => 'true',
            'orientation' => 'top left',
        ],
        'maskOptions' => [
            'alias' => 'dd.mm.yyyy'
        ],
    ]);
?>
```
**with an ActiveForm**
```
<?=
    $form->field($model, 'nameOfField')->widget(
        DatePickerMaskedWidget::className(), [
            'inline' => false,
            'template' => '{addon}{input}',
            'language' => 'fi',
            'clientOptions' => [
                'autoclose' => true,
                'clearBtn' => true,
                'format' => 'dd.mm.yyyy',
                'todayBtn' => 'linked',
                'todayHighlight' => 'true',
                'weekStart' => '1',
                'calendarWeeks' => 'true',
                'orientation' => 'top left',
            ],
            'maskOptions' => [
                'alias' => 'dd.mm.yyyy'
            ],

        ]
    );
?>
```
------> WITHOUT A MODEL <------
```
<?php
use sguinfocom\DatePickerMaskedWidget\DatePickerMaskedWidget;
?>
<?=
    DatePickerMaskedWidget::widget([
        'name' => 'test',
        'value' => '30-16-2016'
        'attribute' => false,
        'template' => '{addon}{input}',
        'language' => 'fi',
        'clientOptions' => [
            'autoclose' => true,
            'clearBtn' => true,
            'format' => 'dd.mm.yyyy',
            'todayBtn' => 'linked',
            'todayHighlight' => 'true',
            'weekStart' => '1',
            'calendarWeeks' => 'true',
            'orientation' => 'top left',
        ],
        'maskOptions' => [
            'alias' => 'dd.mm.yyyy'
        ],
    ]);
?>
```
####Date Range Picker
-----------------

------> WITHOUT A MODEL <------
```
<?php
use sguinfocom\DatePickerMaskedWidget\DateRangePickerMaskedWidget;
?>
<?=
    DateRangePickerMaskedWidget::widget([
        'name' => 'test',
        'value' => '30-16-2016'
        'attribute' => false,
        'template' => '{addon}{input}',
        'language' => 'fi',
        'clientOptions' => [
            'autoclose' => true,
            'clearBtn' => true,
            'format' => 'dd.mm.yyyy',
            'todayBtn' => 'linked',
            'todayHighlight' => 'true',
            'weekStart' => '1',
            'calendarWeeks' => 'true',
            'orientation' => 'top left',
        ],
        'maskOptions' => [
            'alias' => 'dd.mm.yyyy'
        ],
    ]);
?>
```
------> WITH A MODEL <------
```
<?php
use sguinfocom\DatePickerMaskedWidget\DateRangePickerMaskedWidget;
?>
<?=
    $form->field($model, 'startDate')->widget(DateRangePickerMaskedWidget::className(), [
        'attributeTo' => 'endDate',
        'form' => $form, // best for correct client validation
        'language' => 'es',
        'size' => 'lg',
        'clientOptions' => [
            'autoclose' => true,
            'format' => 'dd-M-yyyy'
        ]
    ]);
?>
```
--------------------------------------------------------------------------------

Further Information
-------------------
Please, check the [Bootstrap DatePicker site](http://bootstrap-datepicker.readthedocs.org/en/release/) documentation for further information about its configuration options.
Or the [Masked Input Demo](http://demos.krajee.com/masked-input) for examples on how to use the masks.
Read the source files in order to understand how the widget works.

License
-------

The BSD License (BSD). Please see [License File](LICENSE.md) for more information.

Credits
-------

- [Diana Giova](https://github.com/dianakaal)
