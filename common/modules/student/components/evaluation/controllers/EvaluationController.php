<?php

namespace common\modules\student\components\evaluation\controllers;

use common\models\EmptyCheck;
use common\models\User;
use common\modules\student\components\evaluation\models\CommentForm;
use common\modules\student\components\evaluation\models\MarkForm;
use common\modules\student\components\evaluation\models\UploadForm;
use common\modules\student\models\Teacher;
use common\modules\student\validators\PortfolioFormValidator;
use common\services\NamesManagementService;
use Yii;
use yii\base\DynamicModel;
use yii\filters\AccessControl;
use yii\filters\Cors;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\UploadedFile;

class EvaluationController extends Controller
{
    public $role;
    private NamesManagementService $namesManagementService;

    public function __construct($id, $module, NamesManagementService $namesManagementService, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->namesManagementService = $namesManagementService;
    }

    public function beforeAction($action)
    {

        if (isset(Yii::$app->user->identity) && Yii::$app->user->identity->isInRole(User::ROLE_STUDENT)) {
            $this->role = User::ROLE_STUDENT;
        } elseif (isset(Yii::$app->user->identity) && Yii::$app->user->identity->isInRole(User::ROLE_TEACHER)) {
            $this->role = User::ROLE_TEACHER;
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
                            'ap',
                            'comment',
                            'delete-file',
                            'delete-portfolio',
                            'dictionary',
                            'evaluation',
                            'file',
                            'form',
                            'index',
                            'mark-list',
                            'mark',
                            'students',
                            'upload',
                        ],
                        'allow' => true,
                        'roles' => [
                            User::ROLE_TEACHER,
                            User::ROLE_STUDENT,
                        ]
                    ],
                ]
            ],
            'corsFilter' => [
                'class' => Cors::class,
            ],
        ];
    }

    const ATTR_READONLY = 1;
    const ATTR_REQUIRED = 2;
    const ATTR_HYPERLINK = 3;
    const ATTR_VISIBLE = 4;
    const ATTR_MULTILINE = 6;

    public function actionForm()
    {
        $fields_with_label = [];

        $luid = Yii::$app->request->post('luid');
        $puid = Yii::$app->request->post('puid');
        $lcuid = Yii::$app->request->post('lcuid');
        $uid = Yii::$app->request->post('uid');
        $caf_id = Yii::$app->request->post('caf_id');

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

        $fields = ['luid', 'puid', 'lcuid', 'uid', 'caf_id'];

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

        $startLength = count($fields_with_label);

        $PropertyAttributeValue = $this->namesManagementService->getPropertyAttributeValueColumnName();
        $PropertyAttributeName = $this->namesManagementService->getPropertyAttributeNameColumnName();

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
                        if ($attribute->{$PropertyAttributeValue}) {
                            $attributeValue = self::ATTR_READONLY;
                        }
                        break;

                    case 'ПроверкаЗаполнения':
                        if ($attribute->{$PropertyAttributeValue}) {
                            $attributeValue = self::ATTR_REQUIRED;
                        }
                        break;

                    case 'МногострочныйРежим':
                        if ($attribute->{$PropertyAttributeValue})
                            $attributeValue = self::ATTR_MULTILINE;
                        break;

                    case 'Гиперссылка':
                        if ($attribute->{$PropertyAttributeValue}) {
                            $attributeValue = self::ATTR_HYPERLINK;
                        }
                        break;

                    case 'Видимость':
                        if (!$attribute->{$PropertyAttributeValue}) {
                            $attributes = array_diff($attributes, [self::ATTR_VISIBLE]);
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

            if (strpos($prop->ValueType, 'СправочникСсылка.') !== false) {
                $fields_with_label[$I] = [
                    'name' => $this->getLatinName($property), 'label' => $description,
                    'type' => 'Справочник',
                    'Ref' => $prop->ValueType,
                    'attributes' => $attributes,
                    'Filters' => $prop->Filters,
                    'value' => isset($prop->ValueRef) ? $prop->ValueRef->ReferenceUID : '',
                    'text' => isset($prop->ValueRef) ? $prop->ValueRef->ReferenceName : '',
                ];
            } elseif ($prop->ValueType == 'Дата') {
                $fields_with_label[$I] = [
                    'name' => $this->getLatinName($property), 'label' => $description,
                    'type' => $prop->ValueType, 'attributes' => $attributes,
                    'value' => isset($prop->Value) ? date('d.m.Y', strtotime($prop->Value)) : null,
                ];
            } else {
                $fields_with_label[$I] = [
                    'name' => $this->getLatinName($property), 'label' => $description,
                    'type' => $prop->ValueType, 'attributes' => $attributes,
                    'value' => isset($prop->Value) ? $prop->Value : null,
                ];
            }
            $fields[] = $this->getLatinName($property);

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
        $model->caf_id = $caf_id;

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('@common/modules/student/components/evaluation/views/_add-form', [
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
                    'caf_id' => $model->caf_id,
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
        $model->caf_id = Yii::$app->request->post('caf_id');

        $model->load(Yii::$app->request->post());
        $file = UploadedFile::getInstance($model, 'file');
        if (!empty($file) && Yii::$app->request->isPost) {
            $txt = base64_encode(file_get_contents($file->tempName));

            $ext = pathinfo($file->name);

            $answer = [
                'ResultRef' => [
                    'ReferenceName' => '',
                    'ReferenceId' => '',
                    'ReferenceUID' => $model->uid,
                    'ReferenceClassName' => 'Справочник.Объекты'
                ],
                'BinaryFile' => [
                    'Name' => $ext['filename'],
                    'Description' => $model->description,
                    'Size' => $file->size,
                    'Extension' => $ext['extension'],
                    'Data' => $txt
                ],
                'FileRef' => [
                    'ReferenceName' => '',
                    'ReferenceId' => '',
                    'ReferenceUID' => '',
                    'ReferenceClassName' => '',
                ]
            ];

            Yii::$app->getPortfolioService->saveAttachedFile($answer);

            if (!empty($model->studentId)) {
                return redirect(['/student/evaluation', 'studentId' => $model->studentId, 'luid' => $model->luid, 'puid' => $model->puid, 'caf_id' => $model->caf_id]);
            } else {
                return redirect(['/student/evaluation', 'luid' => $model->luid, 'puid' => $model->puid, 'caf_id' => $model->caf_id]);
            }
        }


        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('@common/modules/student/components/evaluation/views/_add-file', ['model' => $model]);
        } else {
            if (!Yii::$app->request->isAjax && Yii::$app->request->isPost && empty($file)) {
                Yii::$app->session->setFlash('warning', 'Не был прикреплён файл');
            }
            if (!empty($model->studentId)) {
                return redirect([
                    '/student/evaluation',
                    'luid' => $model->luid,
                    'puid' => $model->puid,
                    'caf_id' => $model->caf_id,
                    'plan_id' => $model->plan_id,
                    'studentId' => $model->studentId
                ]);
            } else {
                return redirect(['/student/portfolio', 'luid' => $model->luid, 'puid' => $model->puid, 'recordbook_id' => $model->recordbook_id]);
            }
        }

        return redirect(['/student/portfolio', 'luid' => $model->luid, 'puid' => $model->puid, 'caf_id' => $model->caf_id]);
    }

    public function actionComment()
    {
        $model = new CommentForm();
        $model->uid = Yii::$app->request->post('uid');
        $model->luid = Yii::$app->request->post('luid');
        $model->puid = Yii::$app->request->post('puid');
        $model->studentId = Yii::$app->request->post('studentId');
        $model->caf_id = Yii::$app->request->post('cafId');
        $model->plan_id = Yii::$app->request->post('planId');

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

                if (!is_array($recordbooks)) {
                    $recordbooks = [$recordbooks];
                }

                foreach ($recordbooks as $recordbook) {
                    if ($recordbook->RecordbookId == $model->caf_id) {
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


            if (!empty($model->studentId)) {
                return redirect([
                    '/student/evaluation', 'studentId' => $model->studentId, 'luid' => $model->luid, 'puid' => $model->puid,
                    'caf_id' => $model->caf_id, 'plan_id' => $model->plan_id
                ]);
            } else {
                return redirect([
                    '/student/evaluation', 'luid' => $model->luid, 'puid' => $model->puid, 'caf_id' => $model->caf_id, 'plan_id' => $model->plan_id
                ]);
            }
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('@common/modules/student/components/evaluation/views/_add-comment', [
                'model' => $model
            ]);
        } else {
            if (!Yii::$app->request->isAjax && Yii::$app->request->isPost) {
                Yii::$app->session->setFlash('warning', 'Не был добавлен комментарий');
            }
            if (!empty($model->studentId)) {
                return redirect([
                    '/student/evaluation',
                    'luid' => $model->luid,
                    'puid' => $model->puid,
                    'caf_id' => $model->caf_id,
                    'plan_id' => $model->plan_id,
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
        $model->cafId = (int)Yii::$app->request->post('cafId');

        $model->load(Yii::$app->request->post());

        if (!empty($model->mark)) {
            $guid = Yii::$app->user->identity->guid;
            $teacher = new Teacher([
                'guid' => $guid,
                'departmentSerialNumber' => $model->cafId
            ]);

            $res = Yii::$app->getPortfolioService->saveMark([
                'Teacher' => $teacher->buildFor1C(),

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
                'studentId' => $model->studentId, 'luid' => $model->luid, 'puid' => $model->puid,
                'cafId' => $model->cafId,
            ]);
        }

        $marks = Yii::$app->getPortfolioService->loadAllowedRatingSystems([
            'StatementRef' => [
                'ReferenceName' => '',
                'ReferenceId' => '',
                'ReferenceUID' => $model->statementId,
                'ReferenceClassName' => 'Документ.Ведомость',
            ]
        ]);


        $types = array_column(
            is_array($marks->return->References) ? $marks->return->References : [$marks->return->References],
            'ReferenceName',
            'ReferenceUID'
        );

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('@common/modules/student/components/evaluation/views/_add-mark', [
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

    public function actionIndex($studentId = null, $puid = null, $luid = null, $caf_id = null, $plan_id = null)
    {
        $post_buffer = Yii::$app->request->post();
        if (Yii::$app->request->isAjax) {
            $puid = $post_buffer['puid'];
            $luid = $post_buffer['luid'];
            $caf_id = $post_buffer['caf_id'];
            $plan_id = $post_buffer['plan_id'];
            $studentId = $post_buffer['studentId'];
        }

        $portfolioLoader = Yii::$app->getModule('student')->portfolioLoader;
        $portfolio = $portfolioLoader->loadLapResults($puid, $luid);
        if (isset($post_buffer['ref_UID'], $post_buffer['table_button_submit'])) {
            $portfolio_buffer = json_decode(json_encode($portfolio), true);
            if ($post_buffer['table_button_submit'] == 'save') {
                $portfolio_buffer = Yii::$app->portfolioTable->rewritePortfolio($portfolio_buffer, $post_buffer['new_table'], $post_buffer['ref_UID'], $post_buffer['stringIndex']);
            } elseif ($post_buffer['table_button_submit'] == 'delete' && count($post_buffer['selection']) > 0) {
                $portfolio_buffer = Yii::$app->portfolioTable->deletePortfolio($portfolio_buffer, $post_buffer['new_table'], $post_buffer['selection'], $post_buffer['ref_UID'], $post_buffer['stringIndex']);
            } elseif ($post_buffer['table_button_submit'] == 'add') {
                $portfolio_buffer = Yii::$app->portfolioTable->addPortfolio($portfolio_buffer, $post_buffer['ref_UID'], $post_buffer['stringIndex']);
            }
            if (empty($post_buffer['stringIndex'])) {
                $result = Yii::$app->getPortfolioService->saveLapResult([
                    'PlanUID' => $post_buffer['puid'],
                    'LapUID' => $post_buffer['luid'],
                    'LapResult' => $portfolio_buffer['return']['LapResultStrings']
                ]);
            } else {
                $result = Yii::$app->getPortfolioService->saveLapResult([
                    'PlanUID' => $post_buffer['puid'],
                    'LapUID' => $post_buffer['luid'],
                    'LapResult' => $portfolio_buffer['return']['LapResultStrings'][$post_buffer['stringIndex']]
                ]);
            }
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
        $_states = [];
        if (isset($states)) {
            $_states = $states->return->EmployerState;
        }
        if (!empty($_states) && empty($caf_id)) {
            $caf_id = 0;
        }
        $caf_list = [];
        if (!is_array($_states)) {
            $_states = [$_states];
        }
        foreach ($_states as $i => $state) {
            $caf_list[] = ['id' => $i, 'name' => $state->JobDescription];
        }
        $_curriculums = [];
        if (isset($_states[$caf_id])) {
            $curriculums = Yii::$app->getPortfolioService->loadEmployersCurriculums([
                'EmployerRef' => $user,
                'EmployerState' => json_decode(json_encode($_states[$caf_id]), true)
            ]);

            if (isset($curriculums->return, $curriculums->return->Curriculum)) {
                $_curriculums = $curriculums->return->Curriculum;
            }
        }
        if (!is_array($_curriculums)) {
            $_curriculums = [$_curriculums];
        }
        $curriculums_data = [];
        $cid = null;
        foreach ($_curriculums as $curriculum) {
            $curriculums_data[$curriculum->CurriculumId] = $curriculum->CurriculumName;

            if ($curriculum->CurriculumId == $plan_id) {
                $cid = $curriculum->CurriculumRef;
            }
        }
        $students = [];
        if (!empty($cid)) {
            $students = Yii::$app->getPortfolioService->loadEmployersStudents([
                'EmployerRef' => $user,
                'EmployerState' => json_decode(json_encode($_states[$caf_id]), true),
                'CurriculumRef' => json_decode(json_encode($cid), true),
                'TextFilterType' => 'AnyPosition',
                'Text' => ''
            ]);
        }

        $treeArray = [];

        $portfolio = [];
        $files = [];
        $comments = [];
        $marks = [];
        $recordbook = null;
        $_students = [];
        $error_RecordBook = false;
        if (isset($students, $students->return, $students->return->StudentList) && !empty($studentId)) {
            foreach (is_array($students->return->StudentList) ? $students->return->StudentList : [$students->return->StudentList] as $student) {
                if ($student->Student->ReferenceId == $studentId) {
                    if (isset($student->RecordBook)) {
                        $recordbook = $student->RecordBook;
                    } else {
                        $error_RecordBook = Html::tag('div', '<strong>Ошибка.</strong> Данный студент отчислен или является выпускником.', ['class' => 'alert alert-danger']);
                        break;
                    }
                }
            }
            if (!empty($recordbook)) {

                $planTrees = Yii::$app->getPortfolioService->loadPlanTree(
                    $recordbook->PropertyRecordBook[0]->Value->ReferenceId,
                    'ФизическиеЛица',
                    $recordbook->PropertyRecordBook
                );
                $treeArray = [];
                foreach ($planTrees as $planTree) {
                    $treeArray[] = Yii::$app->treeParser->parseTree($planTree, $puid, $luid);
                }
                $portfolio = $portfolioLoader->loadLapResults($puid, $luid);
                if (isset($portfolio->return, $portfolio->return->LapResultStrings)) {
                    $lapStrings = $portfolio->return->LapResultStrings;

                    if (!empty($lapStrings) && !is_array($lapStrings)) {
                        $lapStrings = [$lapStrings];
                    }
                    foreach ($lapStrings as $port) {
                        $files[$port->Result->ReferenceUID] = $portfolioLoader->loadAttachedFileList($port->Result->ReferenceUID, 'Справочник.Объекты');

                        if (isset($port->Mark)) {
                            $marks[$port->Result->ReferenceUID] = $port->Mark->ReferenceName;
                        }
                    }
                }
                if (isset($port->Result)) {
                    $comment = Yii::$app->getPortfolioService->loadCommentaries(['ObjectRef' => $port->Result]);
                    if (isset($comment->return->CommentaryStrings)) {
                        $_comments = $comment->return->CommentaryStrings;
                        if (!is_array($_comments)) {
                            $_comments = [$_comments];
                        }
                        foreach ($_comments as $_comment) {
                            $comments[$port->Result->ReferenceUID][] = [
                                'date' => $_comment->Period,
                                'author' => $_comment->Author->ReferenceName,
                                'text' => $_comment->Text,
                                'uid' => $_comment->UIDCommentary
                            ];
                        }
                    }
                }
            }
            foreach ($students->return->StudentList ?? [] as $student) {
                $_students[$student->Student->ReferenceId] = $student->Student->ReferenceName;
            }
        }
        return $this->render(
            '@common/modules/student/components/evaluation/views/evaluation',
            [
                'studentId' => $studentId,
                'students' => $_students,

                'treeArray' => $treeArray,

                'portfolio' => $portfolio,

                'files' => $files,
                'marks' => $marks,
                'comments' => $comments,

                'luid' => $luid,
                'puid' => $puid,

                'plan_id' => $plan_id,

                'circullumId' => $plan_id,
                'circullum_data' => $curriculums_data,

                'caf_id' => $caf_id,
                'caf_list' => $caf_list,

                'error_RecordBook' => $error_RecordBook
            ]
        );
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
            $fileName = $info->VolumePathWindows . $info->FilePathInVolume;

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($fileName));
            readfile($fileName);
            exit;
        } else {
            $body = Yii::$app->getPortfolioService->loadBinaryData([
                'FileRef' => [
                    'ReferenceName' => '',
                    'ReferenceId' => '',
                    'ReferenceUID' => $info->Ref->ReferenceUID,
                    'ReferenceClassName' => 'Справочник.Файлы',
                ]
            ]);

            if (empty($body)) {
                return;
            }

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            $fileName = "{$body->return->Name}.{$body->return->Extension}";
            $fileName = iconv('UTF-8', 'CP1251', $fileName);
            header("Content-Disposition: attachment; filename=\"{$fileName}\"");
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . $body->return->Size);

            echo base64_decode($body->return->Data);
            exit;
        }
    }

    public function actionMarkList()
    {
        $params = Yii::$app->request->post('depdrop_all_params');

        if (empty($params)) {
            return json_encode(['output' => [], 'selected' => '']);
        }


        $marks = Yii::$app->getPortfolioService->loadAllowedMarks([
            'RatingSystemRef' => [
                'ReferenceName' => '',
                'ReferenceId' => '',
                'ReferenceUID' => $params['mark-type'],
                'ReferenceClassName' => 'Справочник.СистемыОценивания'
            ]
        ]);

        $marks = array_map(function ($o) {
            return ['id' => $o->ReferenceUID, 'name' => $o->ReferenceName];
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
            if (!is_array($lapProps)) {
                $lapProps = [$lapProps];
            }
            $lapProp = $lapProps[$idx - 1];
            $filters = [];
            if (isset($lapProp->Filters)) {
                $filters = json_decode(json_encode($lapProp->Filters), true);
            }
            $_items = Yii::$app->getPortfolioService->loadReferences([
                'ReferenceClassName' => $type,
                'TextFilterType' => 'AnyPosition',
                'Text' => $q,
                'Filters' => $filters
            ]);
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

    public function actionDeleteFile($uid = null, $idx = null, $luid = null, $puid = null, $caf_id = null)
    {
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

        return redirect(['/student/portfolio', 'luid' => $luid, 'puid' => $puid, 'caf_id' => $caf_id]);
    }

    public function actionDeletePortfolio($uid, $luid, $puid, $caf_id = null)
    {
        $portfolioLoader = Yii::$app->getModule('student')->portfolioLoader;
        $portfolio = $portfolioLoader->loadLapResults($puid, $luid);

        $lapStrings = $portfolio->return->LapResultStrings;
        if (!is_array($lapStrings)) {
            $lapStrings = [$lapStrings];
        }

        foreach ($lapStrings as $item) {
            if ($portfolio->return->LapResultStrings->Result->ReferenceUID != $uid) {
                continue;
            }
            unset($item->LapResultProperties);
            Yii::$app->getPortfolioService->deleteLapResult([
                'PlanUID' => $puid,
                'LapUID' => $luid,
                'LapResult' => json_decode(json_encode($item), true),
            ]);
        }
        return redirect(['/student/portfolio', 'luid' => $luid, 'puid' => $puid, 'caf_id' => $caf_id]);
    }

    public function actionStudents()
    {
        $params = Yii::$app->request->post('depdrop_all_params');
        if (empty($params)) {
            return json_encode(['output' => [], 'selected' => '']);
        }
        $plan_id = $params['plan_id'];
        if ($plan_id == 'Загрузка ...') {
            return json_encode(['output' => [], 'selected' => '']);
        }
        $caf_id = $params['caf_id'];
        if (empty($plan_id)) {
            return json_encode(['output' => [], 'selected' => '']);
        }
        $user_info = Yii::$app->getPortfolioService->loadReference([
            'Parameter' => Yii::$app->user->identity->guid,
            'ParameterType' => 'Код',
            'ParameterRef' => 'Справочник.ФизическиеЛица'
        ]);
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

        if (!is_array($_curriculums)) {
            $_curriculums = [$_curriculums];
        }
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

        if (empty($params)) {
            return json_encode(['output' => [], 'selected' => '']);
        }

        $user_info = Yii::$app->getPortfolioService->loadReference([
            'Parameter' => Yii::$app->user->identity->guid,
            'ParameterType' => 'Код',
            'ParameterRef' => 'Справочник.ФизическиеЛица'
        ]);

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

            if (isset($curriculums->return, $curriculums->return->Curriculum)) {
                $_curriculums = $curriculums->return->Curriculum;
            }
        }

        if (!is_array($_curriculums)) {
            $_curriculums = [$_curriculums];
        }

        $curriculums = [];
        foreach ($_curriculums as $curriculum) {
            $curriculums[] = ['id' => $curriculum->CurriculumId, 'name' => $curriculum->CurriculumName];
        }
        return json_encode(['output' => $curriculums, 'selected' => '']);
    }
}
