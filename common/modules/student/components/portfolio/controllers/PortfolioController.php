<?php

namespace common\modules\student\components\portfolio\controllers;

use common\models\EmptyCheck;
use common\models\User;
use common\modules\student\components\portfolio\models\CommentForm;
use common\modules\student\components\portfolio\models\MarkForm;
use common\modules\student\components\portfolio\models\UploadForm;
use common\modules\student\components\portfolio\PortfolioLoader;
use common\modules\student\validators\PortfolioFormValidator;
use common\services\NamesManagementService;
use Yii;
use yii\base\DynamicModel;
use yii\filters\AccessControl;
use yii\web\UploadedFile;

class PortfolioController extends \yii\web\Controller
{
    const ATTR_READONLY = 1;
    const ATTR_REQUIRED = 2;
    const ATTR_HYPERLINK = 3;
    const ATTR_VISIBLE = 4;
    const ATTR_RECOVERY = 5;
    const ATTR_MULTILINE = 6;

    public $role;
    private NamesManagementService $namesManagementService;

    public function __construct($id, $module, NamesManagementService $namesManagementService, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->namesManagementService = $namesManagementService;
    }

    public function beforeAction($action)
    {

        if (isset(Yii::$app->user->identity) && Yii::$app->user->identity->isInRole(\common\models\User::ROLE_STUDENT)) {
            $this->role = \common\models\User::ROLE_STUDENT;
        } elseif (isset(Yii::$app->user->identity) && Yii::$app->user->identity->isInRole(\common\models\User::ROLE_TEACHER)) {
            $this->role = \common\models\User::ROLE_TEACHER;
        }

        return parent::beforeAction($action);
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => [
                            'index', 'form', 'upload', 'comment', 'evaluation', 'file', 'mark', 'mark-list',
                            'dictionary', 'delete-file', 'delete-portfolio', 'students', 'ap'
                        ],
                        'allow' => true,
                        'roles' => ['student', 'teacher']
                    ],
                ]
            ],
            'corsFilter' => [
                'class' => \yii\filters\Cors::class,
            ],
        ];
    }

    public function actionIndex($luid = '', $puid = '', $recordbook_id = null)
    {
        $recordbooks = [];
        if (Yii::$app->request->post()) {
            $luid = Yii::$app->request->post('DynamicModel')['luid'];
            $puid = Yii::$app->request->post('DynamicModel')['puid'];
            $lcuid = Yii::$app->request->post('DynamicModel')['lcuid'];
            $uid = Yii::$app->request->post('DynamicModel')['uid'];
            $recordbook_id = Yii::$app->request->post('DynamicModel')['recordbook_id'];
            
            $portfolioLoader = Yii::$app->getModule('student')->portfolioLoader;
            $props = $portfolioLoader->loadLapResultClassesProperties(
                $puid,
                $luid,
                $lcuid
            );

            if (empty($props))
                return redirect(['/student/portfolio', 'luid' => $luid, 'puid' => $puid, 'recordbook_id' => $recordbook_id]);

            if (!isset($props->return, $props->return->LapResultStrings, $props->return->LapResultStrings->ResultId))
                return redirect(['/student/portfolio', 'luid' => $luid, 'puid' => $puid, 'recordbook_id' => $recordbook_id]);

            $result_id = $props->return->LapResultStrings->ResultId;
            $_props = $props->return->LapResultStrings;
            if (!empty($uid)) {
                
                $portfolio = $portfolioLoader->loadLapResults($puid, $luid);
                if (isset($portfolio->return, $portfolio->return->LapResultStrings)) {
                    $lapStrings = $portfolio->return->LapResultStrings;
                    if (!empty($lapStrings) && !is_array($lapStrings))
                        $lapStrings = [$lapStrings];
                }
                if (isset($lapStrings))
                    foreach ($lapStrings as $lapString) {
                        if ($lapString->Result->ReferenceUID == $uid) {

                            $_props = $lapString;

                            $result_id = $_props->ResultId;
                            break;
                        }
                    }
            }

            
            $LapResultProperties = [];
            $_post_form = [];
            foreach (Yii::$app->request->post('DynamicModel') as $name => $f) {
                if (!in_array($name, ['luid', 'puid', 'lcuid', 'uid']))
                    $_post_form[$name] = $f;
            }
            if (isset($_props->LapResultProperties)) {
                $lapProps = $_props->LapResultProperties;
            } else {
                $lapProps = [];
            }
            if (!is_array($lapProps))
                $lapProps = [$lapProps];

            
            $buffer = json_decode(json_encode($portfolioLoader->loadLapResults($puid, $luid)), true); 
            

            $PropertyAttributeValue = $this->namesManagementService->getPropertyAttributeValueColumnName();
            $PropertyAttributeName = $this->namesManagementService->getPropertyAttributeNameColumnName();

            $hasError = false;
            foreach ($lapProps as $lp) {
                if (!isset($_post_form[$this->getLatinName($lp->Property->ReferenceName)]))
                    continue;

                if (strpos($lp->ValueType, 'СправочникСсылка.') !== false) {
                    $lp->ValueRef = [
                        'ReferenceName' => '',
                        'ReferenceId' => '',
                        'ReferenceUID' => $_post_form[$this->getLatinName($lp->Property->ReferenceName)],
                        'ReferenceClassName' => str_replace('Ссылка', '', $lp->ValueType)
                    ];
                } elseif ($lp->ValueType == 'Дата') {
                    if (empty($_post_form[$this->getLatinName($lp->Property->ReferenceName)])) {
                        $lp->Value = '';
                    } else {
                        $time = strtotime($_post_form[$this->getLatinName($lp->Property->ReferenceName)]);
                        if ($time > time()) {
                            Yii::$app->session->setFlash('alert', [
                                'body' => '<b>Ошибка.</b> Указанная дата больше текущей. Введённые данные не будут сохранены.',
                                'options' => ['class' => 'alert-danger']
                            ]);
                            $hasError = true;
                            break;
                        }
                        $date = date('Y-m-d', $time);
                        $lp->Value = "{$date}T00:00:00";
                    }
                } elseif ($lp->ValueType == 'Булево') {
                    $lp->Value = $_post_form[$this->getLatinName($lp->Property->ReferenceName)] == '1' ? 'true' : 'false';
                } else {
                    $lp->Value = $_post_form[$this->getLatinName($lp->Property->ReferenceName)]; 
                }

                $temp = json_decode(json_encode($lp), true);
                






                if (isset($buffer, $buffer['return'], $buffer['return']['LapResultStrings'])) {
                    if (isset($buffer['return']['LapResultStrings']['ResultTypeName'])) {
                        $buffer['return']['LapResultStrings'] = [$buffer['return']['LapResultStrings']];
                    }
                    foreach ($buffer['return']['LapResultStrings'] as $LapResultStrings) {
                        if (isset($LapResultStrings['LapResultProperties']['LineNumber'])) {
                            $LapResultStrings['LapResultProperties'] = [$LapResultStrings['LapResultProperties']];
                        }
                        foreach ($LapResultStrings['LapResultProperties'] as $arg) {
                            if ($arg['Property']['ReferenceUID'] === $temp['Property']['ReferenceUID'] && $temp['GroupType'] === 'Table') {
                                foreach ($arg['Attributes'] as $a_key => $attr) {
                                    if (isset($arg['Attributes'][$a_key][$PropertyAttributeName]) && $arg['Attributes'][$a_key][$PropertyAttributeName] == 'ВосстанавливатьСохраненныеЗначения') {
                                        $arg['Attributes'][$a_key][$PropertyAttributeValue] = false;
                                        $arg['ActualAttributes'][$a_key][$PropertyAttributeValue] = false;
                                    }
                                }
                                $temp = $arg;
                            }
                        }
                    }
                }
                
                $LapResultProperties[] = $temp;
            }

            if ($hasError) {
                return redirect(['/student/portfolio', 'luid' => $luid, 'puid' => $puid, 'recordbook_id' => $recordbook_id]);
            }

            $answer = [];
            $answer['LapUID'] = $luid;
            $answer['PlanUID'] = $puid;
            $answer['LapResult'] =
                [
                    'ResultTypeName' => $_props->ResultTypeName,
                    'ResultForm' => json_decode(json_encode($_props->ResultForm), true),
                    'ResultId' => $result_id,

                    'Empty' => false,
                    'Result' => [
                        'ReferenceName' => $_post_form['ref_name'],
                        'ReferenceId' => '',
                        'ReferenceUID' => !empty($uid) ? $uid : '',
                        'ReferenceClassName' => '',
                    ],
                    'LapResultProperties' => $LapResultProperties
                ];
            $result = Yii::$app->getPortfolioService->saveLapResult($answer);
            return redirect(['/student/portfolio', 'luid' => $luid, 'puid' => $puid, 'recordbook_id' => $recordbook_id]);
        }
        
        $portfolioLoader = Yii::$app->getModule('student')->portfolioLoader;
        if ($this->role == User::ROLE_STUDENT) {
            $recordbooks = $portfolioLoader->loadRecordbooks();
            if (!is_array($recordbooks)) {
                $recordbooks = [$recordbooks];
            }
            $props = [];
            if (!empty($recordbooks) && empty($recordbook_id))
                $recordbook_id = $recordbooks[0]->RecordbookId;

            if (!empty($recordbook_id)) {
                foreach ($recordbooks as $recordbook) {
                    if ($recordbook->RecordbookId == $recordbook_id) {
                        $props = $recordbook->PropertyRecordBook;
                        break;
                    }
                }
            }
        }

        if ($this->role == User::ROLE_TEACHER) {
            $user_info = Yii::$app->getPortfolioService->loadReference(
                [
                    'Parameter' => Yii::$app->user->identity->guid,
                    'ParameterType' => 'Код',
                    'ParameterRef' => 'Справочник.ФизическиеЛица'
                ]
            );
            $states = Yii::$app->getPortfolioService->loadEmployerStates([
                'PersonRef' => json_decode(json_encode($user_info->return->Reference), true)
            ]);

            $_states = [];
            if (isset($states, $states->return, $states->return->EmployerState)) {
                $_states = $states->return->EmployerState;
                if (!is_array($_states)) {
                    $_states = [$_states];
                }
            }

            if (!empty($_states) && empty($recordbook_id)) {
                $recordbook_id = 0;
            }

            if (isset($_states[$recordbook_id])) {
                $props = $_states[$recordbook_id]->PropertyEmployerState;
            }

            foreach ($_states as $i => $state) {
                $recordbooks[] = ['RecordbookId' => $i, 'CurriculumName' => $state->JobDescription];
            }
        }

        if (!empty($props)) {
            $planTrees = $portfolioLoader->loadPlanTree(Yii::$app->user->identity->guid, 'ФизическиеЛица', $props);
        }
        $treeArray = [];
        if (!empty($planTrees)) {
            foreach ($planTrees as $planTree) {
                $treeArray[] = Yii::$app->treeParser->parseTree($planTree, $puid, $luid);
            }
        }
        $portfolio = $portfolioLoader->loadLapResults($puid, $luid);
        $types = $portfolioLoader->loadLapResultClasses($puid, $luid);
        $files = [];
        $marks = [];
        if (isset($portfolio->return, $portfolio->return->LapResultStrings)) {
            $lapStrings = $portfolio->return->LapResultStrings;
            if (!empty($lapStrings) && !is_array($lapStrings))
                $lapStrings = [$lapStrings];

            foreach ($lapStrings as $port) {
                $files[$port->Result->ReferenceUID] = $portfolioLoader->loadAttachedFileList($port->Result->ReferenceUID, 'Справочник.Объекты');

                if (isset($port->Mark))
                    $marks[$port->Result->ReferenceUID] = $port->Mark->ReferenceName;
            }
        }

        $comments = [];
        if (isset($port->Result)) {
            $comment = Yii::$app->getPortfolioService->loadCommentaries(['ObjectRef' => $port->Result]);
            if (isset($comment->return->CommentaryStrings)) {
                $_comments = $comment->return->CommentaryStrings;
                if (!is_array($_comments)) {
                    $_comments = [$_comments];
                }
                foreach ($_comments as $_comment)
                    $comments[$port->Result->ReferenceUID][] = [
                        'date' => $_comment->Period,
                        'author' => $_comment->Author->ReferenceName,
                        'text' => $_comment->Text,
                        'uid' => $_comment->UIDCommentary
                    ];
            }
        }
        if (isset($types->return, $types->return->LapResultClass)) {
            if (!is_array($types->return->LapResultClass))
                $types = [$types->return->LapResultClass];
            else
                $types = $types->return->LapResultClass;
        }
        return $this->render('@common/modules/student/components/portfolio/views/portfolio', [
            'treeArray' => $treeArray,
            'portfolio' => $portfolio,
            'types' => $types,
            'files' => $files,
            'comments' => $comments,
            'marks' => $marks,
            'recordbooks' => $recordbooks,
            'recordbook_id' => $recordbook_id,

            'luid' => $luid,
            'puid' => $puid,

            'role' => $this->role,
        ]);
    }

    public function actionForm()
    {
        $fields_with_label = [];

        $luid = Yii::$app->request->post('luid');
        $puid = Yii::$app->request->post('puid');
        $lcuid = Yii::$app->request->post('lcuid');
        $uid = Yii::$app->request->post('uid');
        $recordbook_id = Yii::$app->request->post('recordbook_id');

        
        $portfolioLoader = Yii::$app->getModule('student')->portfolioLoader;

        if (!empty($uid)) {
            

            $portfolio = $portfolioLoader->loadLapResults($puid, $luid);

            if (isset($portfolio->return, $portfolio->return->LapResultStrings)) {

                $lapStrings = $portfolio->return->LapResultStrings;

                if (!empty($lapStrings) && !is_array($lapStrings)) {
                    $lapStrings = [$lapStrings];
                }

                $props = [];

                foreach ($lapStrings as $lapString) {
                    if ($lapString->Result->ReferenceUID == $uid) {
                        $props = $lapString;
                        break;
                    }
                }
            }
        } else {
            $props = $portfolioLoader->loadLapResultClassesProperties(
                $puid,
                $luid,
                $lcuid
            );
        }

        $fields = ['luid', 'puid', 'lcuid', 'uid', 'recordbook_id'];

        if (isset($props->return->LapResultStrings->LapResultProperties)) {
            $props = $props->return->LapResultStrings->LapResultProperties;
        } elseif (isset($props->LapResultProperties)) {
            $value = $props->Result->ReferenceName;
            $props = $props->LapResultProperties;
        } else {
            $props = [];
        }

        $fields[] = 'ref_name';
        $fields_with_label[] = [
            'name' => 'ref_name',
            'label' => 'Наименование',
            'type' => 'Строка',
            'attributes' => [self::ATTR_REQUIRED, self::ATTR_VISIBLE],
            'value' => $value ?? ''
        ];

        if (!is_array($props)) {
            $props = [$props];
        }

        $PropertyAttributeValue = $this->namesManagementService->getPropertyAttributeValueColumnName();
        $PropertyAttributeName = $this->namesManagementService->getPropertyAttributeNameColumnName();

        $startLength = count($fields_with_label);
        foreach ($props as $i => $prop) {
            $I = $startLength + $i;

            $property = $prop->Property->ReferenceName;
            $description = $prop->Description;

            $attributes = [self::ATTR_VISIBLE];
            foreach ($prop->Attributes as $attribute) {
                if (!isset($attribute->{$PropertyAttributeName})) {
                    continue;
                }
                switch ($attribute->{$PropertyAttributeName}) {
                    case 'ТолькоПросмотр':
                        if ($attribute->{$PropertyAttributeValue})
                            $attributeValue = self::ATTR_READONLY;
                        break;

                    case 'ПроверкаЗаполнения':
                        if ($attribute->{$PropertyAttributeValue})
                            $attributeValue = self::ATTR_REQUIRED;
                        break;

                    case 'МногострочныйРежим':
                        if ($attribute->{$PropertyAttributeValue})
                            $attributeValue = self::ATTR_MULTILINE;
                        break;

                    case 'Гиперссылка':
                        if ($attribute->{$PropertyAttributeValue})
                            $attributeValue = self::ATTR_HYPERLINK;
                        break;

                    case 'ВосстанавливатьСохраненныеЗначения':
                        if ($attribute->{$PropertyAttributeValue}) {
                            $attributeValue = self::ATTR_RECOVERY;
                        }
                        break;

                    case 'Видимость':
                        if (!$attribute->{$PropertyAttributeValue})
                            $attributes = array_diff($attributes, [self::ATTR_VISIBLE]);
                        break;

                    default:
                        $attributeValue = '';
                }

                if (!empty($attributeValue)) {
                    $attributes[] = $attributeValue;
                    $attributeValue = '';
                }
            }

            $fields[] = $this->getLatinName($property);
            if (strpos($prop->ValueType, 'СправочникСсылка.') !== false) {
                $fields_with_label[$I] = [
                    'name' => $this->getLatinName($property),
                    'label' => $description,
                    'type' => 'Справочник',
                    'Ref' => $prop->ValueType,
                    'attributes' => $attributes,
                    'Filters' => $prop->Filters,
                    'value' => isset($prop->ValueRef) ? $prop->ValueRef->ReferenceUID : '',
                    'text' => isset($prop->ValueRef) ? $prop->ValueRef->ReferenceName : '',
                ];
            } elseif ($prop->ValueType == 'Дата') {
                $fields_with_label[$I] = [
                    'name' => $this->getLatinName($property),
                    'label' => $description,
                    'type' => $prop->ValueType,
                    'attributes' => $attributes,
                    'value' => isset($prop->Value) ? date('d.m.Y', strtotime($prop->Value)) : null,
                ];
            } elseif ($prop->GroupType == 'Table' && isset($prop->Group->Rows)) {
                $fields_with_label[$I] = [
                    'value' => isset($prop->Value) ? $prop->Value : '',
                    'table_rows' => $prop->Group->Rows,
                    'type' => 'Group',
                    'attributes' => $attributes,
                    'label' => trim((string)$prop->Description),
                    'group_type' => $prop->GroupType,
                    'name' => $this->getLatinName($property),
                    'table_reference_UID' => $prop->Property->ReferenceUID
                ];
            } else {
                $fields_with_label[$I] = [
                    'name' => $this->getLatinName($property),
                    'label' => $description,
                    'type' => $prop->ValueType,
                    'attributes' => $attributes,
                    'value' => isset($prop->Value) ? $prop->Value : null,
                ];
            }

            if (isset($prop->MaxLength)) {
                $prop->MaxLength = (int)$prop->MaxLength;
                $fields_with_label[$I]['maxLength'] = $prop->MaxLength;
            } else {
                $fields_with_label[$I]['maxLength'] = INF;
            }
        }

        $model = new DynamicModel($fields);

        foreach ($fields_with_label as $field) {
            if (in_array(self::ATTR_REQUIRED, $field['attributes'])) {
                $model->addRule(
                    $field['name'],
                    'required',
                    ['message' => "Необходимо заполнить «{$field['label']}»."]
                );
            }

            if (!empty($field['value'])) {

                switch ($field['type']) {
                    case 'Число':
                        $model->{$field['name']} = $field['value'];
                        $model->addRule([$field['name']], 'double')->validate();
                        break;

                    case 'Булево':
                        $model->{$field['name']} = $field['value'] == 'true';
                        break;

                    case 'Дата':
                        $model->{$field['name']} = empty($field['value']) ? '' : $field['value'];
                        break;

                    default:
                        $model->{$field['name']} = $field['value'];
                }
            }

            if (!empty($field['maxLength'])) {
                $model
                    ->addRule(
                        [$field['name']],
                        PortfolioFormValidator::class,
                        ['maxLength' => $field['maxLength']]
                    )
                    ->validate();
            }
        }

        $model->luid = $luid;
        $model->puid = $puid;
        $model->lcuid = $lcuid;
        $model->uid = $uid;
        $model->recordbook_id = $recordbook_id;

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('@common/modules/student/components/portfolio/views/_add-form', [
                'model' => $model,
                'fields_with_label' => $fields_with_label
            ]);
        } else {
            if (!Yii::$app->request->isAjax && Yii::$app->request->isPost) {
                Yii::$app->session->setFlash('warning', 'Не была заполнена форма');
            }
            if (!empty($model->studentId)) {
                return redirect([
                    '/student/evaluation',
                    'luid' => $model->luid,
                    'puid' => $model->puid,
                    'caf_id' => $model->cafId,
                    'plan_id' => $model->planId,
                    'studentId' => $model->studentId
                ]);
            } else {
                return redirect(['/student/portfolio', 'luid' => $model->luid, 'puid' => $model->puid, 'recordbook_id' => $model->recordbook_id]);
            }
        }
    }

    public function actionUpload()
    {
        $model = new UploadForm();
        $model->uid = Yii::$app->request->post('uid');
        $model->luid = Yii::$app->request->post('luid');
        $model->puid = Yii::$app->request->post('puid');
        $model->studentId = Yii::$app->request->post('studentId');
        $model->recordbook_id = Yii::$app->request->post('recordbook_id');

        $model->load(Yii::$app->request->post());
        $file = UploadedFile::getInstance($model, 'file');
        if (
            !empty($file) &&
            !empty($file->tempName) &&
            Yii::$app->request->isPost
        ) {
            $txt = base64_encode(file_get_contents($file->tempName));

            $path = urlencode($file->name);
            $ext = pathinfo($path);
            foreach ($ext as $field => $value) {
                $ext[$field] = urldecode($value);
            }

            $answer = [
                'ResultRef' =>
                [
                    'ReferenceName' => '',
                    'ReferenceId' => '',
                    'ReferenceUID' => $model->uid,
                    'ReferenceClassName' => 'Справочник.Объекты'
                ],
                'BinaryFile' =>
                [
                    'Name' => $ext['filename'],
                    'Description' => $model->description,
                    'Size' => $file->size,
                    'Extension' => $ext['extension'],
                    'Data' => $txt
                ],

                'FileRef' =>
                [
                    'ReferenceName' => '',
                    'ReferenceId' => '',
                    'ReferenceUID' => '',
                    'ReferenceClassName' => '',
                ]

            ];

            Yii::$app->getPortfolioService->saveAttachedFile($answer);


            if (!empty($model->studentId)) {
                return redirect([
                    '/student/evaluation',
                    'studentId' => $model->studentId,
                    'luid' => $model->luid,
                    'puid' => $model->puid,
                    'recordbook_id' => $model->recordbook_id
                ]);
            }

            return redirect([
                '/student/portfolio',
                'luid' => $model->luid,
                'puid' => $model->puid,
                'recordbook_id' => $model->recordbook_id
            ]);
        } elseif (empty($file->tempName)) {
            Yii::error(
                'Невозможно сохранить файл с пустым именем.' . PHP_EOL .
                    print_r($file, true),
                'PortfolioController.actionUpload'
            );
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('@common/modules/student/components/portfolio/views/_add-file', [
                'model' => $model,
            ]);
        } else {
            if (!Yii::$app->request->isAjax && Yii::$app->request->isPost && empty($file)) {
                Yii::$app->session->setFlash('warning', 'Не был прикреплён файл');
            }
            if (!empty($model->studentId)) {
                return redirect([
                    '/student/evaluation',
                    'luid' => $model->luid,
                    'puid' => $model->puid,
                    'caf_id' => $model->cafId,
                    'plan_id' => $model->planId,
                    'studentId' => $model->studentId
                ]);
            } else {
                return redirect(['/student/portfolio', 'luid' => $model->luid, 'puid' => $model->puid, 'recordbook_id' => $model->recordbook_id]);
            }
        }

        return redirect(['/student/portfolio', 'luid' => $model->luid, 'puid' => $model->puid, 'recordbook_id' => $model->recordbook_id]);
    }

    public function actionComment()
    {
        $model = new CommentForm();
        $model->uid = Yii::$app->request->post('uid');
        $model->luid = Yii::$app->request->post('luid');
        $model->puid = Yii::$app->request->post('puid');
        $model->studentId = Yii::$app->request->post('studentId');
        $model->recordbook_id = Yii::$app->request->post('recordbook_id');

        $model->load(Yii::$app->request->post());

        if (!empty($model->comment)) {
            $guid = Yii::$app->user->identity->guid;
            if ($this->role == User::ROLE_TEACHER) {
                $user_info = Yii::$app->getPortfolioService->loadReference(
                    [
                        'Parameter' => $guid,
                        'ParameterType' => 'Код',
                        'ParameterRef' => 'Справочник.ФизическиеЛица'
                    ]
                );

                $rb = $user_info->return->Reference;
            } else {
                $recordbooks = Yii::$app->getPortfolioService->loadRecordbooks($guid);

                if (!is_array($recordbooks))
                    $recordbooks = [$recordbooks];

                foreach ($recordbooks as $recordbook) {
                    if ($recordbook->RecordbookId == $model->recordbook_id) {
                        $rb = $recordbook->PropertyRecordBook[0]->Value;
                        break;
                    }
                }
            }

            $res = Yii::$app->getPortfolioService->saveCommentary([
                'ObjectRef' => [
                    'ReferenceName' => '',
                    'ReferenceId' => '',
                    'ReferenceUID' => $model->uid,
                    'ReferenceClassName' => 'Справочник.Объекты'
                ],
                'Commentary' => [
                    'Period' => date('Y-m-d\TH:i:s'),
                    'Author' => json_decode(json_encode($rb), true),
                    'Text' => $model->comment,
                    'UIDCommentary' => ''
                ]
            ]);
        }
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax(
                '@common/modules/student/components/portfolio/views/_add-comment',
                [
                    'model' => $model
                ]
            );
        } else {
            if (!Yii::$app->request->isAjax && Yii::$app->request->isPost) {
                Yii::$app->session->setFlash('warning', 'Не был добавлен комментарий');
            }
            if (!empty($model->studentId)) {
                return redirect([
                    '/student/evaluation',
                    'luid' => $model->luid,
                    'puid' => $model->puid,
                    'caf_id' => $model->cafId,
                    'plan_id' => $model->planId,
                    'studentId' => $model->studentId
                ]);
            } else {
                return redirect(['/student/portfolio', 'luid' => $model->luid, 'puid' => $model->puid, 'recordbook_id' => $model->recordbook_id]);
            }
        }
    }

    public function actionMark()
    {
        $model = new MarkForm();
        $model->uid = Yii::$app->request->post('uid');
        $model->luid = Yii::$app->request->post('luid');
        $model->puid = Yii::$app->request->post('puid');
        $model->studentId = Yii::$app->request->post('studentId');
        $model->planId = Yii::$app->request->post('planId');
        $model->statementId = Yii::$app->request->post('statementId');

        $model->load(Yii::$app->request->post());

        if (!empty($model->mark)) {
            $user_info = Yii::$app->getPortfolioService->loadReference(
                [
                    'Parameter' => Yii::$app->user->identity->guid,
                    'ParameterType' => 'Код',
                    'ParameterRef' => 'Справочник.ФизическиеЛица'
                ]
            );

            $user = json_decode(json_encode($user_info->return->Reference), true);

            $res = Yii::$app->getPortfolioService->saveMark([
                'TeacherPersonRef' => $user,

                'PlanUID' => $model->puid,
                'LapUID' => $model->luid,

                'ResultRef' => [
                    'ReferenceName' => '',
                    'ReferenceId' => '',
                    'ReferenceUID' => $model->uid,
                    'ReferenceClassName' => 'Справочник.Объекты',
                ],

                'MarkRef' => [
                    'ReferenceName' => '',
                    'ReferenceId' => '',
                    'ReferenceUID' => $model->mark,
                    'ReferenceClassName' => 'Справочник.Отметки'
                ],
                'LessonDate' => date('Y-m-d H:i:s'),
                'StatementRef' => [
                    'ReferenceName' => '',
                    'ReferenceId' => '',
                    'ReferenceUID' => $model->statementId,
                    'ReferenceClassName' => 'Документ.Ведомость',
                ]

            ]);

            return redirect([
                '/student/evaluation', 'plan_id' => $model->planId,
                'studentId' => $model->studentId, 'luid' => $model->luid, 'puid' => $model->puid
            ]);
        }

        $marks = Yii::$app->getPortfolioService->loadAllowedRatingSystems(
            [
                'StatementRef' =>
                [
                    'ReferenceName' => '',
                    'ReferenceId' => '',
                    'ReferenceUID' => $model->statementId,
                    'ReferenceClassName' => 'Документ.Ведомость',
                ]
            ]
        );


        $types = array_column(
            is_array($marks->return->References) ? $marks->return->References : [$marks->return->References],
            'ReferenceName',
            'ReferenceUID'
        );

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('@common/modules/student/components/portfolio/views/_add-mark', [
                'model' => $model,
                'types' => $types
            ]);
        } else {
            if (!Yii::$app->request->isAjax && Yii::$app->request->isPost) {
                Yii::$app->session->setFlash('warning', 'Не была установлена оценка');
            }
            if (!empty($model->studentId)) {
                return redirect([
                    '/student/evaluation',
                    'luid' => $model->luid,
                    'puid' => $model->puid,
                    'caf_id' => $model->cafId,
                    'plan_id' => $model->planId,
                    'studentId' => $model->studentId
                ]);
            } else {
                return redirect(['/student/portfolio', 'luid' => $model->luid, 'puid' => $model->puid, 'recordbook_id' => $model->recordbook_id]);
            }
        }
    }

    public function actionFile($uid = null, $idx = null)
    {
        
        $portfolioLoader = Yii::$app->getModule('student')->portfolioLoader;

        $files = $portfolioLoader->loadAttachedFileList($uid, 'Справочник.Объекты');

        if (is_array($files->return->AttachedFile)) {
            $info = $files->return->AttachedFile[$idx];
        } else {
            $info = $files->return->AttachedFile;
        }

        if ($info->DiskStorage) {
            $pathToFile = (empty($info->VolumePathWindows) ? $info->VolumePathLinux : $info->VolumePathWindows) . $info->FilePathInVolume;
            $fileName = "{$info->Name}.{$info->Extension}";
            if (file_exists($pathToFile)) {
                return Yii::$app->response->sendFile($pathToFile, $fileName);
            }
        }

        $body = Yii::$app->getPortfolioService->loadBinaryData(
            [
                'FileRef' => [
                    'ReferenceName' => '',
                    'ReferenceId' => '',
                    'ReferenceUID' => $info->Ref->ReferenceUID,
                    'ReferenceClassName' => 'Справочник.Файлы',
                ]
            ]
        );

        if (empty($body)) return;

        $fileName = "{$body->return->Name}.{$body->return->Extension}";

        return Yii::$app->response->sendContentAsFile(base64_decode($body->return->Data), $fileName);
    }

    public function actionMarkList()
    {
        $params = Yii::$app->request->post('depdrop_all_params');

        if (empty($params))
            return json_encode(['output' => [], 'selected' => '']);


        $marks = Yii::$app->getPortfolioService->loadAllowedMarks(
            [
                'RatingSystemRef' => [
                    'ReferenceName' => '',
                    'ReferenceId' => '',
                    'ReferenceUID' => $params['mark-type'],
                    'ReferenceClassName' => 'Справочник.СистемыОценивания'
                ]
            ]
        );

        $marks = array_map(function ($o) {
            return ["id" => $o->ReferenceUID, "name" => $o->ReferenceName];
        }, $marks->return->References);

        return json_encode(['output' => $marks, 'selected' => '']);
    }

    public function getLatinName($cyr_name)
    {
        $cyr = [
            'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п',
            'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я',
            'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П',
            'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я'
        ];

        $lat = [
            'a', 'b', 'v', 'g', 'd', 'e', 'io', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p',
            'r', 's', 't', 'u', 'f', 'h', 'ts', 'ch', 'sh', 'sht', 'a', 'i', 'y', 'e', 'yu', 'ya',
            'A', 'B', 'V', 'G', 'D', 'E', 'Io', 'Zh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P',
            'R', 'S', 'T', 'U', 'F', 'H', 'Ts', 'Ch', 'Sh', 'Sht', 'A', 'I', 'Y', 'e', 'Yu', 'Ya'
        ];

        return str_replace($cyr, $lat, $cyr_name);
    }

    public function actionDictionary($luid, $lcuid, $puid, $idx, $q, $type)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            
            $portfolioLoader = Yii::$app->getModule('student')->portfolioLoader;
            $props = $portfolioLoader->loadLapResultClassesProperties(
                $puid,
                $luid,
                $lcuid
            );

            $_props = $props->return->LapResultStrings;

            if (isset($_props->LapResultProperties)) {
                $lapProps = $_props->LapResultProperties;
            } else {
                $lapProps = [];
            }

            if (!is_array($lapProps))
                $lapProps = [$lapProps];


            $lapProp = $lapProps[$idx - 1];


            $filters = [];
            if (isset($lapProp->Filters)) {
                $filters = json_decode(json_encode($lapProp->Filters), true);
            }

            $_items = Yii::$app->getPortfolioService->loadReferences(
                [
                    'ReferenceClassName' => $type,
                    'TextFilterType' => 'AnyPosition',
                    'Text' => $q,
                    'Filters' => $filters
                ]
            );

            $items = [];
            if (isset($_items->return, $_items->return->References)) {

                if (!is_array($_items->return->References)) {
                    $refs = [$_items->return->References];
                } else {
                    $refs = $_items->return->References;
                }

                foreach ($refs as $ref) {
                    $items[] = ['id' => $ref->ReferenceUID, 'text' => $ref->ReferenceName];
                }
            }

            $out['results'] = $items;
        }

        return $out;
    }


    public function actionDeleteFile(
        $uid = null,
        $idx = null,
        $luid = null,
        $puid = null,
        $recordbook_id = null
    ) {
        
        $portfolioLoader = Yii::$app->getModule('student')->portfolioLoader;

        $files = $portfolioLoader->loadAttachedFileList($uid, 'Справочник.Объекты');

        if (is_array($files->return->AttachedFile)) {
            $info = $files->return->AttachedFile[$idx];
        } else {
            $info = $files->return->AttachedFile;
        }

        Yii::$app->getPortfolioService->deleteAttachedFile([

            'ResultRef' => [

                'ReferenceName' => '',
                'ReferenceId' => '',
                'ReferenceUID' => $uid,
                'ReferenceClassName' => 'Справочник.Объекты'
            ],
            'FileRef' => json_decode(json_encode($info->Ref))
        ]);

        return redirect(['/student/portfolio', 'luid' => $luid, 'puid' => $puid, 'recordbook_id' => $recordbook_id]);
    }

    public function actionDeletePortfolio($uid, $luid, $puid, $recordbook_id = null)
    {
        
        $portfolioLoader = Yii::$app->getModule('student')->portfolioLoader;
        $portfolio = $portfolioLoader->loadLapResults($puid, $luid);

        $lapStrings = $portfolio->return->LapResultStrings;
        if (!is_array($lapStrings))
            $lapStrings = [$lapStrings];

        foreach ($lapStrings as $item) {
            if ($item->Result->ReferenceUID != $uid)
                continue;


            unset($item->LapResultProperties);


            Yii::$app->getPortfolioService->deleteLapResult([
                'PlanUID' => $puid,
                'LapUID' => $luid,
                'LapResult' => json_decode(json_encode($item), true),

            ]);
        }

        return redirect(['/student/portfolio', 'luid' => $luid, 'puid' => $puid, 'recordbook_id' => $recordbook_id]);
    }

    public function actionStudents()
    {
        $params = Yii::$app->request->post('depdrop_all_params');

        if (empty($params))
            return json_encode(['output' => [], 'selected' => '']);

        $plan_id = $params['plan_id'];

        if ($plan_id == 'Загрузка ...')
            return json_encode(['output' => [], 'selected' => '']);


        $caf_id = $params['caf_id'];

        if (empty($plan_id)) {
            return json_encode(['output' => [], 'selected' => '']);
        }

        $user_info = Yii::$app->getPortfolioService->loadReference(
            [
                'Parameter' => Yii::$app->user->identity->guid,
                'ParameterType' => 'Код',
                'ParameterRef' => 'Справочник.ФизическиеЛица'
            ]
        );

        $user = json_decode(json_encode($user_info->return->Reference), true);

        $states = Yii::$app->getPortfolioService->loadEmployerStates(['PersonRef' => $user]);

        $state = null;

        if (isset($states->return, $states->return->EmployerState)) {
            if (is_array($states->return->EmployerState)) {
                $state = $states->return->EmployerState[$caf_id];
            } else {
                $state = $states->return->EmployerState;
            }
        }

        $_curriculums = [];
        if (!empty($state)) {
            $curriculums = Yii::$app->getPortfolioService->loadEmployersCurriculums([
                'EmployerRef' => $user,
                'EmployerState' => json_decode(json_encode($state), true)
            ]);

            $_curriculums = $curriculums->return->Curriculum;
        }

        if (!is_array($_curriculums))
            $_curriculums = [$_curriculums];

        $curriculums_data = [];
        $curriculumRef = null;
        foreach ($_curriculums as $curriculum) {
            if ($curriculum->CurriculumId == $plan_id) {
                $curriculumRef = json_decode(json_encode($curriculum->CurriculumRef), true);
                break;
            }
        }

        $students = Yii::$app->getPortfolioService->loadEmployersStudents([
            'EmployerRef' => $user,
            'EmployerState' => json_decode(json_encode($state), true),
            'CurriculumRef' => json_decode(json_encode($curriculumRef), true),
            'TextFilterType' => 'AnyPosition',
            'Text' => ''
        ]);

        $_students = [];

        if (isset($students->return->StudentList) && !EmptyCheck::isEmpty($students->return->StudentList)) {
            foreach (is_array($students->return->StudentList) ? $students->return->StudentList : [$students->return->StudentList] as $student) {
                $_students[] = ['name' => $student->Student->ReferenceName, 'id' => $student->Student->ReferenceId];
            }
        }

        return json_encode(['output' => array_unique($_students, SORT_REGULAR), 'selected' => '']);
    }

    public function actionAp()
    {
        $params = Yii::$app->request->post('depdrop_all_params');

        if (empty($params))
            return json_encode(['output' => [], 'selected' => '']);


        $user_info = Yii::$app->getPortfolioService->loadReference(
            [
                'Parameter' => Yii::$app->user->identity->guid,
                'ParameterType' => 'Код',
                'ParameterRef' => 'Справочник.ФизическиеЛица'
            ]
        );

        $user = json_decode(json_encode($user_info->return->Reference), true);

        $states = Yii::$app->getPortfolioService->loadEmployerStates(['PersonRef' => $user]);

        $_states = [];

        if (isset($states)) {
            $_states = $states->return->EmployerState;
        }

        if (!is_array($_states)) {
            $_states = [$_states];
        }

        $caf_id = $params['caf_id'];

        $_curriculums = [];

        if (isset($_states[$caf_id])) {
            $curriculums = Yii::$app->getPortfolioService->loadEmployersCurriculums([
                'EmployerRef' => $user,
                'EmployerState' => json_decode(json_encode($_states[$caf_id]), true)
            ]);


            if (isset($curriculums->return, $curriculums->return->Curriculum))
                $_curriculums = $curriculums->return->Curriculum;
        }

        if (!is_array($_curriculums))
            $_curriculums = [$_curriculums];

        $curriculums = [];
        foreach ($_curriculums as $curriculum) {
            $curriculums[] = ['id' => $curriculum->CurriculumId, 'name' => $curriculum->CurriculumName];
        }
        return json_encode(['output' => $curriculums, 'selected' => '']);
    }
}
