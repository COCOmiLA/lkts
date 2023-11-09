<?php

namespace backend\models;

use kartik\form\ActiveForm;
use Yii;
use yii\base\DynamicModel;
use yii\helpers\ArrayHelper;












class FiltersSetting extends \yii\db\ActiveRecord
{
    const ENABLE = true;
    const DISABLE = false;

    


    public static function tableName()
    {
        return '{{%filters_setting}}';
    }

    


    public function rules()
    {
        return [
            [
                'serial',
                'integer'
            ],
            [
                [
                    'show_column',
                    'show_filter'
                ],
                'boolean',
            ],
            [
                [
                    'show_column',
                    'show_filter'
                ],
                'default',
                'value' => self::DISABLE
            ],
            [
                [
                    'show_column',
                    'show_filter'
                ],
                'in',
                'range' => [self::ENABLE, self::DISABLE]
            ],
            [
                [
                    'name',
                    'label'
                ],
                'string',
                'max' => 255
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'name' => 'Название',
            'label' => 'Заголовок',
            'serial' => 'Порядковый номер',
            'show_filter' => 'Показывать фильтр',
            'show_column' => 'Показывать колонку',
        ];
    }

    




    public static function loadFromPost(&$model = null)
    {
        $data = Yii::$app->request->post();

        $postData = ArrayHelper::getValue($data, "{$model->formName()}.sortablePageElements");
        $sortablePageElements = [];
        if (isset($postData)) {
            $sortablePageElements = json_decode(
                base64_decode(
                    $postData
                ),
                true
            );
            $sortablePageElements = array_shift($sortablePageElements);
        }

        $postData = ArrayHelper::getValue($data, "{$model->formName()}.showColumn");
        $showColumn = [];
        if (isset($postData)) {
            $showColumn = $postData;
        }

        $postData = ArrayHelper::getValue($data, "{$model->formName()}.showFilter");
        $showFilter = [];
        if (isset($postData)) {
            $showFilter = $postData;
        }

        $saveResults = array_map(
            function ($serial, $name) use ($model, $showColumn, $showFilter) {
                
                $filter = current(
                    array_filter(
                        $model->filters,
                        function ($filter) use ($name) {
                            
                            return $filter->name == $name;
                        }
                    )
                );
                $filter->serial = $serial + 1;

                $columnValue = ArrayHelper::getValue($showColumn, $name);
                if (isset($columnValue)) {
                    $filter->show_column = $columnValue;
                }

                $filterValue = ArrayHelper::getValue($showFilter, $name);
                if (isset($filterValue)) {
                    $filter->show_filter = $filterValue;
                }

                if ($filter->validate()) {
                    return $filter->save();
                }

                return false;
            },
            array_keys($sortablePageElements),
            $sortablePageElements
        );

        if (array_search(false, $saveResults) === false) {
            return true;
        }
        return false;
    }

    





    public function buildField($form = null, $model = null)
    {
        $field = Yii::$app->controller->renderPartial('partials/_field', [
            'form' => $form,
            'model' => $model,
            'filter' => $this,
        ]);
        return [
            'content' => $field,
            'options' => ['data-element_id' => $this->name]
        ];
    }
}
