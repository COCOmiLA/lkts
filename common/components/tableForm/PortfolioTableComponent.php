<?php

namespace common\components\tableForm;

use common\modules\student\models\Teacher;
use common\services\NamesManagementService;
use kartik\builder\TabularForm;
use kartik\dialog\Dialog;
use kartik\icons\Icon;
use kartik\select2\Select2;
use sguinfocom\DatePickerMaskedWidget\DatePickerMaskedWidget;
use stdClass;
use Yii;
use yii\base\Component;
use yii\base\UserException;
use yii\bootstrap4\Modal;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\web\JsExpression;






class PortfolioTableComponent extends Component
{
    const ATTR_VISIBLE = 4;
    const ATTR_READONLY = 1;
    const ATTR_REQUIRED = 2;
    const ATTR_RECOVERY = 5;
    const ATTR_HYPERLINK = 3;
    const ATTR_MULTILINE = 6;

    private const VALUE_TYPE_PATTERN = "/([\x{0410}-\x{042F}]+)(Ссылка)(\\.[\x{0410}-\x{042F}]+)/iu";

    private const TABULAR_VALUE_DATE_TYPE = 'Дата';
    private const TABULAR_VALUE_REFERENCE_TYPE = 'СправочникСсылка';

    private string $LapUID = '';
    private string $PlanUID = '';
    private string $LapResultClassUID = '';

    private NamesManagementService $namesManagementService;

    public function __construct(NamesManagementService $namesManagementService, $config = [])
    {
        parent::__construct($config);
        $this->namesManagementService = $namesManagementService;
    }

    










    public function drawTable(&$portfolio, $puid, $luid, $ref_UID, $stringIndex, $result = null)
    {
        if (isset($portfolio->return, $portfolio->return->LapResultStrings, $result) && $result == 'Success') {
            if (is_array($portfolio->return->LapResultStrings)) {
                $strings = $portfolio->return->LapResultStrings;
            } else {
                $strings = [$portfolio->return->LapResultStrings];
            }
            $string = ArrayHelper::getValue($strings, $stringIndex);
            $rows = $this->dataCollection($string);
            foreach ($rows as $row) {
                if (isset($row['table_rows'], $row['table_reference_UID']) && $row['table_reference_UID'] == $ref_UID) {
                    $tabular_parameters = $this->tabularParameters($row);

                    $parameterPage = [
                        'puid' => $puid,
                        'luid' => $luid,
                        'isAfterAdd' => 'false',
                        'stringIndex' => $stringIndex,
                        'uniqueCardSignature' => ArrayHelper::getValue(Yii::$app->request->post(), 'uniqueCardSignature'),
                    ];
                    $tableButtonSubmit = ArrayHelper::getValue(Yii::$app->request->post(), 'table_button_submit');
                    if (isset($tableButtonSubmit) && $tableButtonSubmit == 'add') {
                        $parameterPage['isAfterAdd'] = 'true';
                    }
                    return Yii::$app->view->renderAjax('@common/components/tableForm/_tabular_form.php', [
                        'row' => $row,
                        'parameter_page' => $parameterPage,
                        'screening' => $tabular_parameters['screening'],
                        'attributes' => $tabular_parameters['attributes'],
                        'dataProvider' => $tabular_parameters['dataProvider'],
                    ]);
                }
            }
            return '<div class="alert alert-danger" role="alert"> Возникла ошибка в работе сервиса. Обратитесь к администратору</div>';
        } else {
            return '<div class="alert alert-danger" role="alert"> Возникла ошибка в работе сервиса. Обратитесь к администратору</div>';
        }
    }

    






    public function dataCollection(&$string)
    {
        $data = [];
        if (isset($string->LapResultProperties)) {
            $lapResultProperties = $string->LapResultProperties;
            if (!is_array($lapResultProperties)) {
                $lapResultProperties = [$lapResultProperties];
            }

            $PropertyAttributeValue = $this->namesManagementService->getPropertyAttributeValueColumnName();
            $PropertyAttributeName = $this->namesManagementService->getPropertyAttributeNameColumnName();

            foreach ($lapResultProperties as $prop) {
                $value = '';
                if (isset($prop->ValueRef)) {
                    $value = $prop->ValueRef->ReferenceName;
                } elseif (isset($prop->Value)) {
                    $value = $prop->Value;
                }
                $attributes = [Yii::$app->portfolioTable::ATTR_VISIBLE];
                if (isset($prop->Attributes)) {
                    foreach ($prop->Attributes as $attribute) {
                        if (isset($attribute->{$PropertyAttributeValue}) && $attribute->{$PropertyAttributeValue}) {
                            switch ($attribute->{$PropertyAttributeName}) {
                                case 'ТолькоПросмотр':
                                    if ($attribute->{$PropertyAttributeValue}) {
                                        $attributeValue = Yii::$app->portfolioTable::ATTR_READONLY;
                                    }
                                    break;
                                case 'ВосстанавливатьСохраненныйеЗначения':
                                    if ($attribute->{$PropertyAttributeValue}) {
                                        $attributeValue = Yii::$app->portfolioTable::ATTR_RECOVERY;
                                    }
                                    break;
                                case 'ПроверкаЗаполнения':
                                    if ($attribute->{$PropertyAttributeValue}) {
                                        $attributeValue = Yii::$app->portfolioTable::ATTR_REQUIRED;
                                    }
                                    break;
                                case "МногострочныйРежим":
                                    if ($attribute->{$PropertyAttributeValue})
                                        $attributeValue = Yii::$app->portfolioTable::ATTR_MULTILINE;
                                    break;
                                case 'Гиперссылка':
                                    if ($attribute->{$PropertyAttributeValue}) {
                                        $attributeValue = Yii::$app->portfolioTable::ATTR_HYPERLINK;
                                    }
                                    break;
                                case 'Видимость':
                                    if (!$attribute->{$PropertyAttributeValue}) {
                                        $attributes = array_diff($attributes, [Yii::$app->portfolioTable::ATTR_VISIBLE]);
                                    }
                                    break;
                                default:
                                    $attributeValue = '';
                            }
                            if (!empty($attributeValue)) {
                                $attributes[] = $attributeValue;
                                $attributeValue = '';
                            }
                        }
                    }
                }
                $table = null;
                $table_reference_UID = null;
                $table_reference_UID = $prop->Property->ReferenceUID;
                if ($prop->GroupType == 'Table' && isset($prop->Group->Rows)) {
                    $table = $prop->Group->Rows;
                } elseif ($prop->GroupType == 'Table') {
                    $table = 'isEmpty';
                }
                $data[] = [
                    'value' => $value,
                    'table_rows' => $table,
                    'type' => $prop->ValueType,
                    'attributes' => $attributes,
                    'group_type' => $prop->GroupType,
                    'line_number' => $prop->LineNumber,
                    'name' => trim((string)$prop->Description),
                    'table_reference_UID' => $table_reference_UID
                ];
            }
        }
        return $data;
    }

    





    public function tabularParameters($row, $canEdit = true)
    {
        $screening = rand();

        $data_table = [];
        $table_header = [];
        $thisIs_refType = [];
        $refType_filters = [];
        $refType_classes = [];
        $thisIs_dateType = [];

        if (!is_array($row['table_rows'])) {
            $row['table_rows'] = [$row['table_rows']];
        }
        foreach ($row['table_rows'] as $rows) {
            $buffer = [];
            if (!is_array($rows->Columns)) {
                $rows->Columns = [$rows->Columns];
            }
            foreach ($rows->Columns as $cols) {
                $table_header[md5($cols->Description)] = $cols->Description;
                if (!isset($cols->Value) && isset($cols->ValueRef)) {
                    $cols->Value = $cols->ValueRef->ReferenceName;
                }

                if (mb_strpos($cols->ValueType, PortfolioTableComponent::TABULAR_VALUE_REFERENCE_TYPE) !== false) {
                    $refType_filters[md5($cols->Description)] = base64_encode(json_encode($cols->Filters));
                    if (preg_match(
                        PortfolioTableComponent::VALUE_TYPE_PATTERN,
                        $cols->ValueType,
                        $matches
                    )) {
                        $refType_classes[md5($cols->Description)] = "{$matches[1]}{$matches[3]}";
                    }
                    $thisIs_refType[$cols->Description] = '';
                    $buffer[md5($cols->Description)] = base64_encode(json_encode([
                        'ReferenceName' => '',
                        'ReferenceId' => '',
                        'ReferenceUID' => '',
                        'ReferenceClassName' => '',
                    ]));
                } elseif ($cols->ValueType == PortfolioTableComponent::TABULAR_VALUE_DATE_TYPE) {
                    $buffer[md5($cols->Description)] = date('Y-m-d');
                    $thisIs_dateType[] = $cols->Description;
                } else {
                    $buffer[md5($cols->Description)] = '';
                }

                if (!empty($cols->Value)) {
                    if ($cols->ValueType == PortfolioTableComponent::TABULAR_VALUE_DATE_TYPE) {
                        $buffer[md5($cols->Description)] = Yii::$app->formatter->asDate(strtotime($cols->Value), 'yyyy-MM-dd');
                        $thisIs_dateType[] = $cols->Description;
                    } else {
                        $buffer[md5($cols->Description)] = $cols->Value;
                    }
                }
                if (isset($cols->ValueRef)) {
                    $thisIs_refType[$cols->Description] = $cols->ValueRef->ReferenceName;
                    $buffer[md5($cols->Description)] = base64_encode(json_encode([
                        'ReferenceName' => $cols->ValueRef->ReferenceName,
                        'ReferenceId' => $cols->ValueRef->ReferenceId,
                        'ReferenceUID' => $cols->ValueRef->ReferenceUID,
                        'ReferenceClassName' => $cols->ValueRef->ReferenceClassName,
                    ]));
                }
            }
            if ($canEdit) {
                $data_table[$rows->RowNumber - 1] = array_merge(['RowNumber' => $rows->RowNumber], $buffer);
            } else {
                $data_table[$rows->RowNumber - 1] = $buffer;
            }
        }
        ksort($table_header);
        ksort($data_table);
        $dataProvider = new ArrayDataProvider([
            'allModels' => $data_table,
        ]);
        $attributes = [];
        foreach ($table_header as $hKey => $head) {
            $attributes[$hKey] = ['label' => $head];
            if (in_array($head, $thisIs_dateType)) {
                $attributes[$hKey] = [
                    'label' => $head,
                    'type' => TabularForm::INPUT_WIDGET,
                    'widgetClass' => DatePickerMaskedWidget::class,
                    'options' => [
                        'inline' => false,
                        'language' => 'ru',
                        'template' => '{input}{addon}',
                        'id' => 'date_picker_masked_widget_' . rand(),
                        'pluginOptions' => [
                            'format' => 'yyyy-mm-dd',
                            'todayHighlight' => true,
                            'autoclose' => true
                        ],
                        'clientOptions' => [
                            'clearBtn' => false,
                            'weekStart' => '1',
                            'autoclose' => true,
                            'todayBtn' => 'linked',
                            'format' => 'dd.mm.yyyy',
                            'calendarWeeks' => 'true',
                            'todayHighlight' => 'true',
                            'orientation' => 'top left',
                        ],
                        'maskOptions' => [
                            'alias' => 'dd.mm.yyyy'
                        ],
                    ]
                ];
            }
            if (in_array($head, array_keys($thisIs_refType))) {
                if ($canEdit) {
                    $attributes[$hKey] = [
                        'label' => $head,
                        'type' => TabularForm::INPUT_WIDGET,
                        'widgetClass' => Select2::class,
                        'options' => function ($model) use ($hKey, $screening, $refType_filters, $refType_classes) {
                            $initValueText = ArrayHelper::getValue(
                                json_decode(base64_decode($model[$hKey])),
                                'ReferenceName'
                            );
                            return [
                                'initValueText' => empty($initValueText) ? ' ' : $initValueText,
                                'options' => [
                                    'id' => 'select_2_' . rand(),
                                    'placeholder' => 'Введите запрос для поиска ...',
                                ],
                                'pluginOptions' => [
                                    'dropdownParent' => "#_tabular_form_table$screening",
                                    'allowClear' => true,
                                    'minimumInputLength' => 5,
                                    'language' => [
                                        'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                                    ],
                                    'ajax' => [
                                        'cache' => true,
                                        'dataType' => 'json',
                                        'url' => '/site/selected-tabular-element',
                                        'data' => new JsExpression("
                                        function(params) {
                                            return {
                                                query: params.term,
                                                filters: '{$refType_filters[$hKey]}',
                                                ref_class: '{$refType_classes[$hKey]}',
                                            };
                                        }
                                    ")
                                    ],
                                    'escapeMarkup' => new JsExpression('function (params) { return params; }'),
                                    'templateResult' => new JsExpression('function(params) { return params.text; }'),
                                    'templateSelection' => new JsExpression('function (params) { return params.text; }'),
                                ],
                            ];
                        }
                    ];
                } else {
                    $attributes[$hKey] = [
                        'label' => $head,
                        'type' => TabularForm::INPUT_TEXT,
                        'staticValue' => function ($model) use ($hKey) {
                            $value = $model[$hKey] ?? '';
                            return ArrayHelper::getValue(
                                json_decode(base64_decode($value)),
                                'ReferenceName',
                                $value
                            );
                        }
                    ];
                }
            }
        }
        if ($canEdit) {
            $attributes['RowNumber'] = [
                'label' => '',
                'type' => TabularForm::INPUT_HIDDEN
            ];
        }
        return [
            'screening' => $screening,
            'attributes' => $attributes,
            'dataProvider' => $dataProvider
        ];
    }

    






    public function modalForTable($row, $parameter_page, $canEdit = true)
    {
        $pathToSvg = FileHelper::normalizePath('/frontend/web/install/img/twotone-table_chart-24px.svg');
        if ($row['table_rows'] == 'isEmpty' && isset($row['line_number'])) {
            $LapResultStringsClass = Yii::$app->getPortfolioService->loadLapResultClassesProperties(
                $this->PlanUID,
                $this->LapUID,
                $this->LapResultClassUID
            );

            if (
                isset($LapResultStringsClass) &&
                isset($LapResultStringsClass->return) &&
                isset($LapResultStringsClass->return->LapResultStrings) &&
                isset($LapResultStringsClass->return->LapResultStrings->LapResultProperties)
            ) {
                $LapResultProperties = $LapResultStringsClass->return->LapResultStrings->LapResultProperties;
                if (!is_array($LapResultProperties)) {
                    $LapResultProperties = [$LapResultProperties];
                }

                foreach ($LapResultProperties as $LapResultProperty) {
                    if ($LapResultProperty->LineNumber != $row['line_number']) {
                        continue;
                    }

                    if ($LapResultProperty->GroupType == 'Table' && isset($LapResultProperty->Group->Rows)) {
                        $row['table_rows'] = $LapResultProperty->Group->Rows;
                    }
                }
            }
        }

        $tabular_parameters = $this->tabularParameters($row, $canEdit);
        Modal::begin([
            'title' => Html::tag('h5', $row['name']),
            'size' => 'modal-md',
            'toggleButton' => [
                'tag' => 'button',
                'label' => "<img src=\"{$pathToSvg}\" width=\"24 px\" height=\"24px\">",
                'class' => 'btn-sm btn-primary'
            ]
        ]);
        echo Yii::$app->view->render('@common/components/tableForm/_tabular_form.php', [
            'row' => $row,
            'canEdit' => $canEdit,
            'parameter_page' => $parameter_page,
            'screening' => $tabular_parameters['screening'],
            'attributes' => $tabular_parameters['attributes'],
            'dataProvider' => $tabular_parameters['dataProvider'],
        ]);
        Modal::end();
    }

    





    public function dropDownListButton($types)
    {
        if (!empty($types) && is_array($types)) {
            echo Html::beginTag('div', ['class' => 'row']);
            echo Html::beginTag('div', ['class' => 'col-12']);
            echo Html::beginTag('div', ['class' => 'btn-group float-right form-group']);
            echo Html::tag(
                'button',
                'Добавить',
                [
                    'type' => 'button',
                    'aria-haspopup' => 'true',
                    'aria-expanded' => 'false',
                    'data-toggle' => 'dropdown',
                    'class' => 'btn btn-outline-secondary dropdown-toggle'
                ]
            );
            echo Html::beginTag(
                'ul',
                [
                    'id' => 'drop-menu',
                    'style' => 'width: 200px',
                    'class' => 'dropdown-menu dropdown-menu-with-old-style',
                ]
            );
            foreach ($types as $class) {
                echo Html::tag(
                    'li',
                    Html::a($class->ReferenceName, '#'),
                    [
                        'style' => 'padding: 4px 0',
                        'data-lcuid' => $class->ReferenceUID
                    ]
                );
            }
            echo Html::endTag('ul');
            echo Html::endTag('div');
            echo Html::endTag('div');
            echo Html::endTag('div');
        }
    }

    








    public function editButtons($string, $recordbook_id, $luid, $puid)
    {
        echo Html::a(
            'Удалить',
            [
                'luid' => $luid,
                'puid' => $puid,
                'recordbook_id' => $recordbook_id,
                '/student/portfolio/delete-portfolio',
                'uid' => $string->Result->ReferenceUID
            ],
            [
                'class' => 'btn btn-sm btn-danger float-right',
                'data' => ['confirm' => 'Вы подтверждаете удаление портфолио?']
            ]
        );
        echo Html::a(
            'Редактировать',
            '#',
            [
                'style' => 'margin-right: 10px',
                'data-uid' => $string->Result->ReferenceUID,
                'data-lcuid' => $string->ResultForm->ReferenceUID,
                'class' => 'btn btn-sm btn-primary float-right edit-result'

            ]
        );
    }

    









    public function deleteButton(stdClass $string, int $recordbook_id, string $luid, string $puid, int $i)
    {
        echo Html::a(
            'Удалить',
            [
                'idx' => $i,
                'luid' => $luid,
                'puid' => $puid,
                '/student/portfolio/delete-file',
                'recordbook_id' => $recordbook_id,
                'uid' => $string->Result->ReferenceUID
            ],
            [
                'class' => 'btn-sm btn-danger float-right',
                'data' => [
                    'confirm' => 'Вы подтверждаете удаление файла?',
                ],
            ]
        );
    }

    
















    public function additionalInfoBlock(array $marks, stdClass $string, array $comments, array $allFiles, int $recordbook_id, string $luid, string $puid, string $portfolio_type)
    {
        if ($portfolio_type == 'portfolio') {
            
            echo Html::beginTag('div', ['class' => 'row']);
            echo Html::beginTag('div', ['class' => 'col-12 col-md-6']);
            echo Html::tag('h5', 'Оценка:');
            echo Html::endTag('div');
            if (isset($marks[$string->Result->ReferenceUID])) {
                echo Html::beginTag('div', ['class' => 'col-12 col-md-6']);
                echo Html::tag(
                    'h5',
                    Html::tag(
                        'b',
                        $marks[$string->Result->ReferenceUID]
                    )
                );
                echo Html::endTag('div');
            }
            echo Html::endTag('div');

            
            if (isset($string->Teachers)) {
                echo Html::beginTag('div', ['class' => 'row']);
                echo Html::beginTag('div', ['class' => 'col-12 col-md-6']);
                echo Html::tag('h5', 'Преподаватели:');
                echo Html::endTag('div');

                if (!is_array($string->Teachers)) {
                    $string->Teachers = [$string->Teachers];
                }

                echo Html::beginTag('div', ['class' => 'col-12 col-md-6']);
                foreach ($string->Teachers as $rawTeacher) {
                    

                    echo Html::beginTag('div', ['class' => 'row']);
                    echo Html::beginTag('div', ['class' => 'col-12']);
                    echo Html::tag(
                        'h5',
                        Html::tag(
                            'b',
                            (new Teacher(['rawTeacher' => $rawTeacher]))->renderTeacher()
                        )
                    );
                    echo Html::endTag('div');
                    echo Html::endTag('div');
                }
                echo Html::endTag('div');
                echo Html::endTag('div');
            }


            
            if (isset($string->Status, $string->Status->ReferenceName)) {
                echo Html::beginTag('div', ['class' => 'row']);

                echo Html::beginTag('div', ['class' => 'col-12 col-md-6']);
                echo Html::tag('h5', 'Статус документа:');
                echo Html::endTag('div');

                echo Html::beginTag('div', ['class' => 'col-12 col-md-6']);
                echo Html::tag(
                    'h5',
                    Html::tag(
                        'b',
                        $string->Status->ReferenceName
                    )
                );
                echo Html::endTag('div');

                echo Html::endTag('div');
            }
        }

        
        echo Html::tag('h5', 'Файлы:');
        echo Html::beginTag(
            'table',
            ['class' => 'table table-striped table-bordered', 'style' => 'padding-top: 20px; font-size: 80%']
        );
        foreach ($allFiles as $i => $file) {
            echo Html::beginTag('tr');
            echo Html::beginTag('td');
            echo Html::a(
                    !empty($file->Description) ? $file->Description : "{$file->Name}.{$file->Extension}",
                    [
                        '/student/portfolio/file',
                        'uid' => $string->Result->ReferenceUID,
                        'idx' => $i,
                    ],
                    [
                        'target' => 'blank',
                        'data-pjax' => 0
                    ]
                ) . '  (' . Yii::$app->formatter->asShortSize($file->Size) . ') ';
            if ($portfolio_type != 'umk') {
                $this->deleteButton($string, $recordbook_id, $luid, $puid, $i);
            }
            echo Html::endTag('td');
            echo Html::endTag('tr');
        }
        echo Html::endTag('table');
        if ($portfolio_type != 'umk') {
            echo Html::beginTag('div', ['class' => 'row']);
            echo Html::beginTag('div', ['class' => 'col-12']);
            echo Html::a(
                'Добавить файл',
                '#',
                [
                    'data-uid' => $string->Result->ReferenceUID,
                    'class' => 'btn-sm btn-primary float-right upload-file'
                ]
            );
            echo Html::endTag('div');
            echo Html::endTag('div');
        }
        if ($portfolio_type == 'portfolio' || $portfolio_type == 'evaluation') {

            
            echo Html::tag('h5', 'Комментарии:');
            echo Html::beginTag('div', ['class' => 'row']);
            echo Html::beginTag('div', ['class' => 'col-12']);
            if (isset($comments[$string->Result->ReferenceUID])) {
                echo Html::beginTag(
                    'table',
                    ['class' => 'table table-striped table-bordered', 'style' => 'padding-top: 20px; font-size: 80%']
                );
                foreach ($comments[$string->Result->ReferenceUID] as $comment) {
                    echo Html::beginTag('tr');
                    echo Html::beginTag('td', ['style' => 'width: 40%;']);
                    echo Html::tag('p', Html::tag('b', $comment['author']), ['style' => 'margin-bottom: 0;']);
                    echo Html::tag('p', Yii::$app->formatter->asDatetime(strtotime($comment['date'])));
                    echo Html::endTag('td');
                    echo Html::beginTag('td');
                    echo Html::tag('p', $comment['text'], ['class' => 'portfolio_breakable_comment', 'style' => 'font-size: 1.5rem;']);
                    echo Html::endTag('td');
                    echo Html::endTag('tr');
                }
                echo Html::endTag('table');
            }
            echo Html::endTag('div');
            echo Html::beginTag('div', ['class' => 'col-12']);
            echo Html::a(
                'Добавить комментарий',
                '#',
                [
                    'data-uid' => $string->Result->ReferenceUID,
                    'class' => 'btn-sm btn-primary float-right add-comment'
                ]
            );
            echo Html::endTag('div');
            echo Html::endTag('div');
        }
    }

    






    public function evaluationButton($marks, $string)
    {
        echo Html::beginTag('div', ['class' => 'row']);
        echo Html::beginTag('div', ['class' => 'col-12 col-md-6']);
        echo Html::tag('h5', 'Оценивание:');
        echo Html::endTag('div');
        if (isset($marks[$string->Result->ReferenceUID])) {
            echo Html::beginTag('div', ['class' => 'col-12 col-md-6']);
            echo Html::tag('b', $marks[$string->Result->ReferenceUID]);
            echo Html::endTag('div');
        } else {
            echo Html::beginTag('div', ['class' => 'col-12 col-md-6']);
            echo Html::a('Оценить', '#', [
                'data-uid' => $string->Result->ReferenceUID,
                'data-statementid' => (isset($string->Statement) ? $string->Statement->ReferenceUID : ''),
                'class' => 'btn-sm btn-primary float-right add-mark'
            ]);
            echo Html::endTag('div');
        }
        echo Html::endTag('div');
    }

    










    public function check(?string $studentId, ?string $plan_id, ?int $discipline_id, string $portfolio_type)
    {
        if ($portfolio_type == 'portfolio') {
            return true;
        } elseif ($portfolio_type == 'evaluation') {
            return !empty($studentId);
        } elseif ($portfolio_type == 'umk') {
            return isset($plan_id, $discipline_id);
        }
        return false;
    }

    







    public function portfolioTableShow($param_array = [], $portfolio_type = null)
    {
        if (empty($param_array) || $portfolio_type == null) {
            return;
        }

        echo Dialog::widget(['options' => []]);

        
        
        $luid = (isset($param_array['luid'])) ? $param_array['luid'] : '';                                 
        $puid = (isset($param_array['puid'])) ? $param_array['puid'] : '';                                 
        $types = (isset($param_array['types'])) ? $param_array['types'] : null;                              
        $files = (isset($param_array['files'])) ? $param_array['files'] : null;                              
        $marks = (isset($param_array['marks'])) ? $param_array['marks'] : [];                                
        $caf_id = (isset($param_array['caf_id'])) ? $param_array['caf_id'] : null;                           
        $plan_id = (isset($param_array['plan_id'])) ? $param_array['plan_id'] : null;                        
        $comments = (isset($param_array['comments'])) ? $param_array['comments'] : [];                       
        $portfolio = (isset($param_array['portfolio'])) ? $param_array['portfolio'] : null;                  
        $studentId = (isset($param_array['studentId'])) ? $param_array['studentId'] : null;                  
        $recordbook_id = (isset($param_array['recordbook_id'])) ? (int)$param_array['recordbook_id'] : 0;    
        $discipline_id = (isset($param_array['discipline_id'])) ? (int)$param_array['discipline_id'] : null; 
        
        

        $this->LapUID = $luid;
        $this->PlanUID = $puid;

        if ($portfolio_type == 'portfolio') {
            $this->dropDownListButton($types);
        }
        $strings = [];
        if (isset($portfolio->return, $portfolio->return->LapResultStrings)) {
            if (is_array($portfolio->return->LapResultStrings)) {
                $strings = $portfolio->return->LapResultStrings;
            } else {
                $strings = [$portfolio->return->LapResultStrings];
            }
        }
        if (empty($strings) && $this->check($studentId, $plan_id, $discipline_id, $portfolio_type)) {
            if ((strlen((string)$puid) && strlen((string)$luid)) || (strlen((string)$discipline_id) + strlen((string)$recordbook_id) + strlen((string)$studentId)) < 1) {
                $message = 'Для выбранных параметров нет данных.';
            } else {
                $message = 'Для отображения данных выберите элемент в дереве слева.';
            }
            echo Html::tag(
                'div',
                $message,
                ['class' => 'alert alert-info', 'role' => 'alert']
            );
        }
        foreach ($strings as $stringIndex => $string) {
            if ($this->LapResultClassUID != $string->ResultForm->ReferenceUID) {
                $this->LapResultClassUID = $string->ResultForm->ReferenceUID;
            }

            $uniqueCardSignature = md5(json_encode($string, JSON_FORCE_OBJECT));
            $data = [];
            $data[] = [
                'name' => 'Наименование',
                'value' => $string->Result->ReferenceName,
                'type' => 'text',
                'attributes' => [$this::ATTR_VISIBLE],
                'group_type' => (isset($string->GroupType)) ? $string->GroupType : 'NoGroup'
            ];
            $data = array_merge($data, $this->dataCollection($string));
            echo Html::beginTag('div', ['style' => 'border: 1px solid var(--gray); border-radius: 4px; padding: 20px; margin-bottom:25px']);
            if ($portfolio_type == 'portfolio') $this->editButtons($string, $recordbook_id, $luid, $puid);
            echo Html::beginTag(
                'table',
                [
                    'class' => 'table table-striped table-bordered',
                    'style' => ($portfolio_type == 'portfolio') ? 'margin-top: 50px; ' : '' . 'font-size: 80%;'
                ]
            );
            foreach ($data as $row) {
                if (!in_array($this::ATTR_VISIBLE, $row['attributes'])) {
                    continue;
                }
                echo Html::beginTag('tr');
                echo Html::tag('th', $row['name'], ['style' => 'width: 20%']);
                echo Html::beginTag('td');
                if ($row['group_type'] == 'Table' && isset($row['table_rows'])) {
                    $this->modalForTable(
                        $row,
                        [
                            'puid' => $puid,
                            'luid' => $luid,
                            'caf_id' => $caf_id,
                            'plan_id' => $plan_id,
                            'studentId' => $studentId,
                            'stringIndex' => $stringIndex,
                            'uniqueCardSignature' => $uniqueCardSignature,
                        ],
                        $portfolio_type != 'umk'
                    );
                } elseif (in_array($this::ATTR_HYPERLINK, $row['attributes'])) {
                    echo Html::a($row['value'], 'http://' . $row['value'], ['target' => '_blank']);
                } elseif ($row['type'] == 'Дата') {
                    if (!empty($row['value'])) {
                        echo Yii::$app->formatter->asDate($row['value']);
                    }
                } elseif ($row['type'] == 'Булево') {
                    echo $row['value'] == 'true' ? Icon::show('check') : '';
                } elseif (in_array($this::ATTR_MULTILINE, $row['attributes'])) {
                    echo nl2br($row['value']);
                } else {
                    echo $row['value'];
                }
                echo Html::endTag('td');
                echo Html::endTag('tr');
            }
            echo Html::endTag('table');
            if ($portfolio_type == 'evaluation') {
                $this->evaluationButton($marks, $string);
            }
            Yii::$app->formatter->sizeFormatBase = 1000;
            $allFiles = [];
            if (isset($files[$string->Result->ReferenceUID]->return->AttachedFile)) {
                $allFiles = $files[$string->Result->ReferenceUID]->return->AttachedFile;
                if (!is_array($allFiles)) {
                    $allFiles = [$allFiles];
                }
            }
            $this->additionalInfoBlock($marks, $string, $comments, $allFiles, $recordbook_id, $luid, $puid, $portfolio_type);
            echo Html::endTag('div');
        }
    }

    









    public function addPortfolio($portfolio_buffer, $ref_UID, $stringIndex = '')
    {
        $uniqueCardSignature = ArrayHelper::getValue(Yii::$app->request->post(), 'uniqueCardSignature');
        if (empty($stringIndex)) {
            $key = ArrayHelper::getValue($portfolio_buffer, 'return.LapResultStrings.LapResultProperties');
        } else {
            $key = ArrayHelper::getValue($portfolio_buffer, "return.LapResultStrings.{$stringIndex}.LapResultProperties");
        }
        if (empty($key)) {
            foreach (ArrayHelper::getValue($portfolio_buffer, 'return.LapResultStrings') as $stringNumber => $string) {
                if ($uniqueCardSignature == md5(json_encode($string, JSON_FORCE_OBJECT))) {
                    $portfolio_buffer->return->LapResultStrings = $string;
                    $key = ArrayHelper::getValue($portfolio_buffer, 'return.LapResultStrings.LapResultProperties');
                    break;
                }
            }
        }

        $PropertyAttributeValue = $this->namesManagementService->getPropertyAttributeValueColumnName();
        $PropertyAttributeName = $this->namesManagementService->getPropertyAttributeNameColumnName();

        $key = array_keys($key);
        for ($i = 0; $i < count($key); $i++) {
            if (empty($stringIndex)) {
                $lap_res = ArrayHelper::getValue($portfolio_buffer, "return.LapResultStrings.LapResultProperties.{$key[$i]}");
            } else {
                $lap_res = ArrayHelper::getValue($portfolio_buffer, "return.LapResultStrings.{$stringIndex}.LapResultProperties.{$key[$i]}");
            }
            if ($lap_res->Property->ReferenceUID != $ref_UID) {
                continue;
            }
            foreach ($lap_res->Attributes as $a_key => $attr) {
                if (isset($lap_res->Attributes[$a_key]->{$PropertyAttributeName}) && $lap_res->Attributes[$a_key]->{$PropertyAttributeName} == 'ВосстанавливатьСохраненныеЗначения') {
                    $lap_res->Attributes[$a_key]->{$PropertyAttributeValue} = false;
                    $lap_res->ActualAttributes[$a_key]->{$PropertyAttributeValue} = false;
                }
            }

            if (isset($lap_res->Group->Rows->RowNumber)) {
                $lap_res->Group->Rows = [$lap_res->Group->Rows];
            }
            
            $new_field = json_decode(json_encode($lap_res->Group->Rows[0]));

            $max = 0;
            foreach ($lap_res->Group->Rows as $row) {
                if ($row->RowNumber > $max) {
                    $max = $row->RowNumber;
                }
            }
            if (isset($new_field->Columns->LineNumber)) {
                $new_field->Columns = [$new_field->Columns];
            }
            $nf_key = array_keys($new_field->Columns);
            for ($j = 0; $j < count($nf_key); $j++) {
                if (isset($new_field->Columns[$nf_key[$j]], $new_field->Columns[$nf_key[$j]]->Value)) {
                    $new_field->Columns[$nf_key[$j]]->Value = '';
                }
                if (isset($new_field->Columns[$nf_key[$j]], $new_field->Columns[$nf_key[$j]]->ValueRef)) {
                    $new_field->Columns[$nf_key[$j]]->ValueRef = (object)[
                        'ReferenceName' => '',
                        'ReferenceId' => '',
                        'ReferenceUID' => '',
                        'ReferenceClassName' => '',
                    ];
                }
            }

            $new_field->RowNumber = $max + 1;
            $lap_res->Group->Rows[] = $new_field;

            if (empty($stringIndex)) {
                $portfolio_buffer->return->LapResultStrings->LapResultProperties[$key[$i]] = $lap_res;
            } else {
                $portfolio_buffer->return->LapResultStrings[$stringIndex]->LapResultProperties[$key[$i]] = $lap_res;
            }
        }
        return $portfolio_buffer;
    }

    private function addGroupFromLapResultClassUID($lap_res, $LapResultClassUID)
    {
        if (!$LapResultClassUID) {
            throw new UserException("Нет идентификатора типа карточки.");
        }

        $post_buffer = Yii::$app->request->post();
        $portfolioClasses = Yii::$app->getPortfolioService->loadLapResultClassesProperties(
            $post_buffer['puid'],
            $post_buffer['luid'],
            $LapResultClassUID
        );
        $count = $lap_res['LineNumber'] - 1;
        $Group = ArrayHelper::getValue($portfolioClasses, "return.LapResultStrings.LapResultProperties.{$count}.Group");
        if (!$Group) {
            throw new UserException("Невозможно восстановить структуру.");
        }

        if (isset($Group->Rows->RowNumber)) {
            $Group->Rows = [$Group->Rows];
        }
        
        $new_field = json_decode(json_encode($Group->Rows[0]), true);

        $max = 0;
        foreach ($Group->Rows as $row) {
            if ($row->RowNumber > $max) {
                $max = $row->RowNumber;
            }
        }
        if (isset($new_field['Columns']->LineNumber)) {
            $new_field['Columns'] = [$new_field['Columns']];
        }
        $nf_key = array_keys($new_field['Columns']);
        for ($j = 0; $j < count($nf_key); $j++) {
            $cols = $new_field['Columns'][$nf_key[$j]];
            if (mb_strpos($cols['ValueType'], PortfolioTableComponent::TABULAR_VALUE_REFERENCE_TYPE) !== false) {
                $new_field['Columns'][$nf_key[$j]]['ValueRef'] = (object)[
                    'ReferenceName' => '',
                    'ReferenceId' => '',
                    'ReferenceUID' => '',
                    'ReferenceClassName' => '',
                ];
            } elseif ($cols['ValueType'] == PortfolioTableComponent::TABULAR_VALUE_DATE_TYPE) {
                $new_field['Columns'][$nf_key[$j]]['Value'] = date('Y-m-d');
            } else {
                $new_field['Columns'][$nf_key[$j]]['Value'] = '';
            }
        }

        return [$new_field];
    }

    









    public function deletePortfolio($portfolio, &$new_table, $selection, $ref_UID, $stringIndex = '')
    {
        $uniqueCardSignature = ArrayHelper::getValue(Yii::$app->request->post(), 'uniqueCardSignature');
        if (empty($stringIndex)) {
            $key = ArrayHelper::getValue($portfolio, 'return.LapResultStrings.LapResultProperties');
        } else {
            $key = ArrayHelper::getValue($portfolio, "return.LapResultStrings.{$stringIndex}.LapResultProperties");
        }
        if (empty($key)) {
            foreach (ArrayHelper::getValue($portfolio, 'return.LapResultStrings') as $stringNumber => $string) {
                if ($uniqueCardSignature == md5(json_encode($string, JSON_FORCE_OBJECT))) {
                    $portfolio->return->LapResultStrings = $string;
                    $key = ArrayHelper::getValue($portfolio, 'return.LapResultStrings.LapResultProperties');
                    break;
                }
            }
        }
        $PropertyAttributeValue = $this->namesManagementService->getPropertyAttributeValueColumnName();
        $PropertyAttributeName = $this->namesManagementService->getPropertyAttributeNameColumnName();

        $key = array_keys($key);
        $portfolio_buffer = json_decode(json_encode($portfolio), true);
        $key = array_keys($key);
        for ($i = 0; $i < count($key); $i++) {
            if (empty($stringIndex)) {
                $lap_res = ArrayHelper::getValue($portfolio_buffer, "return.LapResultStrings.LapResultProperties.{$key[$i]}");
            } else {
                $lap_res = ArrayHelper::getValue($portfolio_buffer, "return.LapResultStrings.{$stringIndex}.LapResultProperties.{$key[$i]}");
            }
            if ($lap_res['Property']['ReferenceUID'] != $ref_UID) {
                continue;
            }
            foreach ($lap_res['Attributes'] as $a_key => $attr) {
                if (isset($lap_res['Attributes'][$a_key][$PropertyAttributeName]) && $lap_res['Attributes'][$a_key][$PropertyAttributeName] == 'ВосстанавливатьСохраненныеЗначения') {
                    $lap_res['Attributes'][$a_key][$PropertyAttributeValue] = false;
                    $lap_res['ActualAttributes'][$a_key][$PropertyAttributeValue] = false;
                }
            }

            




            $rows = (isset($lap_res['Group']['Rows']['RowNumber'])) ? [$lap_res['Group']['Rows']] : $lap_res['Group']['Rows'];
            $lap_res['Group']['Rows'] = [];
            $j = 0;
            foreach ($rows as $r_key => $row) {
                foreach ($selection as $num_del) {
                    if ($row['RowNumber'] == $new_table[$num_del]['RowNumber']) {
                        unset($rows[$r_key]);
                        break;
                    }
                }
                if (array_key_exists($r_key, $rows)) {
                    $lap_res['Group']['Rows'][$j] = $row;
                    $lap_res['Group']['Rows'][$j]['RowNumber'] = $j + 1;
                    $j++;
                }
            }

            if (empty($stringIndex)) {
                ArrayHelper::setValue($portfolio_buffer, "return.LapResultStrings.LapResultProperties.{$key[$i]}", $lap_res);
            } else {
                ArrayHelper::setValue($portfolio_buffer, "return.LapResultStrings.{$stringIndex}.LapResultProperties.{$key[$i]}", $lap_res);
            }
        }
        return $portfolio_buffer;
    }

    








    public function rewritePortfolio($portfolio, &$new_table, $ref_UID, $stringIndex = '')
    {
        $isAfterAdd = ArrayHelper::getValue(Yii::$app->request->post(), 'isAfterAdd');
        $isAfterAdd = empty($isAfterAdd) ? false : (bool)$isAfterAdd;
        if ($isAfterAdd) {
            $portfolio = $this->addPortfolio($portfolio, $ref_UID, $stringIndex);
        }
        $uniqueCardSignature = ArrayHelper::getValue(Yii::$app->request->post(), 'uniqueCardSignature');
        if (empty($stringIndex)) {
            $key = ArrayHelper::getValue($portfolio, 'return.LapResultStrings.LapResultProperties');
        } else {
            $key = ArrayHelper::getValue($portfolio, "return.LapResultStrings.{$stringIndex}.LapResultProperties");
        }
        if (empty($key)) {
            foreach (ArrayHelper::getValue($portfolio, 'return.LapResultStrings') as $stringNumber => $string) {
                if ($uniqueCardSignature == md5(json_encode($string, JSON_FORCE_OBJECT))) {
                    $portfolio->return->LapResultStrings = $string;
                    $key = ArrayHelper::getValue($portfolio, 'return.LapResultStrings.LapResultProperties');
                    break;
                }
            }
        }
        $key = array_keys($key);
        $portfolio_buffer = json_decode(json_encode($portfolio), true);

        $LapResultClassUID = null;
        $PropertyAttributeValue = $this->namesManagementService->getPropertyAttributeValueColumnName();
        $PropertyAttributeName = $this->namesManagementService->getPropertyAttributeNameColumnName();
        for ($i = 0; $i < count($key); $i++) {
            if (empty($stringIndex)) {
                $LapResultClassUID = ArrayHelper::getValue($portfolio_buffer, "return.LapResultStrings.ResultForm.ReferenceUID");
            } else {
                $LapResultClassUID = ArrayHelper::getValue($portfolio_buffer, "return.LapResultStrings.{$stringIndex}.ResultForm.ReferenceUID");
            }
            if (empty($stringIndex)) {
                $lap_res = ArrayHelper::getValue($portfolio_buffer, "return.LapResultStrings.LapResultProperties.{$key[$i]}");
            } else {
                $lap_res = ArrayHelper::getValue($portfolio_buffer, "return.LapResultStrings.{$stringIndex}.LapResultProperties.{$key[$i]}");
            }
            if ($lap_res['Property']['ReferenceUID'] != $ref_UID) {
                continue;
            }

            if ($lap_res['GroupType'] == 'Table' && !isset($lap_res['Group']['Rows'])) {
                $lap_res['Group']['Rows'] = $this->addGroupFromLapResultClassUID($lap_res, $LapResultClassUID);
            }
            foreach ($lap_res['Attributes'] as $a_key => $attr) {
                if (isset($lap_res['Attributes'][$a_key][$PropertyAttributeName]) && $lap_res['Attributes'][$a_key][$PropertyAttributeName] == 'ВосстанавливатьСохраненныеЗначения') {
                    $lap_res['Attributes'][$a_key][$PropertyAttributeValue] = false;
                    $lap_res['ActualAttributes'][$a_key][$PropertyAttributeValue] = false;
                }
                $lap_res['Attributes'][$a_key][$PropertyAttributeValue] = $lap_res['ActualAttributes'][$a_key][$PropertyAttributeValue];
            }
            $lap_res['Group']['Rows'] = (isset($lap_res['Group']['Rows']['RowNumber'])) ? [$lap_res['Group']['Rows']] : $lap_res['Group']['Rows'];
            if (array_key_exists('RowNumber', $lap_res['Group']['Rows'])) {
                $lap_res['Group']['Rows'] = [$lap_res['Group']['Rows']];
            }
            foreach ($lap_res['Group']['Rows'] as $r_key => $row) {
                if (array_key_exists('LineNumber', $row['Columns'])) {
                    $row['Columns'] = [$row['Columns']];
                    $lap_res['Group']['Rows'][$r_key]['Columns'] = [$lap_res['Group']['Rows'][$r_key]['Columns']];
                }
                foreach ($row['Columns'] as $c_key => $col) {
                    $isValue = ArrayHelper::getValue($lap_res, "Group.Rows.{$r_key}.Columns.{$c_key}.Value");
                    $isValueRef = ArrayHelper::getValue($lap_res, "Group.Rows.{$r_key}.Columns.{$c_key}.ValueRef");
                    if (isset($isValue)) {
                        $lap_res['Group']['Rows'][$r_key]['Columns'][$c_key]['Value'] = $new_table[$row['RowNumber'] - 1][md5($col['Description'])];
                    }
                    if (isset($isValueRef)) {
                        $lap_res['Group']['Rows'][$r_key]['Columns'][$c_key]['ValueRef'] = json_decode(base64_decode($new_table[$row['RowNumber'] - 1][md5($col['Description'])]), true);
                    }
                }
            }

            if (empty($stringIndex)) {
                ArrayHelper::setValue($portfolio_buffer, "return.LapResultStrings.LapResultProperties.{$key[$i]}", $lap_res);
            } else {
                ArrayHelper::setValue($portfolio_buffer, "return.LapResultStrings.{$stringIndex}.LapResultProperties.{$key[$i]}", $lap_res);
            }
        }

        return $portfolio_buffer;
    }
}
