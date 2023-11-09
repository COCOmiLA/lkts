<?php

namespace common\modules\abiturient\modules\admission\controllers;


use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use common\modules\abiturient\modules\admission\models\ListCompetitionHeader;
use common\modules\abiturient\modules\admission\models\ListCompetitionRow;
use Yii;
use yii\filters\AccessControl;


class AdmissionController extends \yii\web\Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'totalbudget', 'speciality', 'competition', 'chance', 'totallist', 'competitionlist', 'specialitylist', 'chancelist'],
                        'allow' => true,
                        'roles' => ['?']
                    ],
                    [
                        'actions' => ['index', 'totalbudget', 'speciality', 'competition', 'chance', 'totallist', 'competitionlist', 'specialitylist', 'chancelist', 'showlist', 'showchancelist'],
                        'allow' => true,
                        'roles' => ['@']
                    ],
                ]
            ],
            'corsFilter' => [
                'class' => \yii\filters\Cors::class,
            ],
        ];
    }

    public function beforeAction($action)
    {
        Yii::$app->assetManager->baseUrl = Yii::$app->homeUrl . "/assets";
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionTotalbudget()
    {
        $data = Yii::$app->getModule('student')->getModule('admission')->admissionLoader->loadTotalAbitBudget();
        if (Yii::$app->request->isPost) {
            $request = Yii::$app->request;
            $search_params = [
                'financeForm' => $request->post("financeForm"),
                'institute' => $request->post("institute"),
                'learnForm' => $request->post("learnForm"),
                'spec' => $request->post("spec"),
                'qualification' => $request->post("qualification")
            ];
            if ($request->post("financeForm") == "0") {
                $data = Yii::$app->getModule('student')->getModule('admission')->admissionLoader->loadTotalAbitBudget($search_params);
                return $this->renderPartial('_totalbudget', ['data' => $data]);
            } else {
                $data = Yii::$app->getModule('student')->getModule('admission')->admissionLoader->loadTotalAbit($search_params);
                return $this->renderPartial('_total', ['data' => $data]);
            }
        }
        $qualifications = [
            0 => 'бакалавриат/специалитет',
            1 => 'магистратура',
            2 => 'аспирантура'
        ];
        $learnForms = [
            0 => 'очная',
            1 => 'заочная',
        ];
        $finance_forms = [
            0 => 'бюджет',
            1 => 'внебюджет',
        ];
        $specs = Yii::$app->getModule('student')->getModule('admission')->admissionLoader->getTotalSpec();
        $temp_institutes = array_map(function ($o) {
            return $o->department;
        }, $data->rows);
        $temp_institutes = array_unique(array_filter($temp_institutes));
        $institutes = [];
        foreach ($temp_institutes as $inst) {
            $institutes[$inst] = $inst;
        }
        return $this->renderPartial('totalbudget', [
            'qualifications' => $qualifications,
            'institutes' => $institutes,
            'learnForms' => $learnForms,
            'finance_forms' => $finance_forms,
            'specs' => $specs,
        ]);
    }

    public function actionSpeciality()
    {
        if (Yii::$app->request->isPost) {
            $request = Yii::$app->request;
            $search_params = [
                'code' => $request->post("code"),
                'fio' => $request->post("fio"),
                'qualification' => $request->post("qualification")
            ];
            $data = Yii::$app->getModule('student')->getModule('admission')->admissionLoader->loadSpeciality($search_params);
            return $this->renderPartial('_speciality', ['data' => $data]);
        }
        $qualifications = [
            0 => 'бакалавриат/специалитет',
            1 => 'магистратура',
            2 => 'аспирантура'
        ];
        $fios = Yii::$app->getModule('student')->getModule('admission')->admissionLoader->getSpecFios();
        $codes = Yii::$app->getModule('student')->getModule('admission')->admissionLoader->getSpecCodes();
        return $this->renderPartial('speciality', ['qualifications' => $qualifications, 'fios' => $fios, 'codes' => $codes]);
    }

    public function actionCompetition()
    {
        if (Yii::$app->request->isPost) {
            $request = Yii::$app->request;
            $search_params = [
                'financeForm' => $request->post("financeForm"),
                'fio' => $request->post("fio"),
                'institute' => $request->post("institute"),
                'learnForm' => $request->post("learnForm"),
                'qualification' => $request->post("qualification"),
                'spec' => $request->post("spec"),
            ];
            $data = ListCompetitionHeader::findOne([
                'finance_code' => $search_params['financeForm'],
                'institute' => $search_params['institute'],
                'learnform_code' => $search_params['learnForm'],
                'qualification' => ListCompetitionHeader::getQualificationText($search_params['qualification']),
                'speciality' => $search_params['spec'],
            ]);
            $fio = null;
            
            $fio = $request->post("fio");
            $code = $request->post("fio");
            

            return $this->renderPartial('_competition', ['data' => $data, 'fio' => $fio, 'code' => $code]);

        }
        $qualifications = [
            0 => 'Специалист/бакалавр',
            1 => 'Магистр',
            2 => 'Аспирант'
        ];
        $learnForms = [
            1 => 'Очная',
            2 => 'Очно-заочная',
            3 => 'Заочная'
        ];
        $finance_forms = [
            
            2 => 'Внебюджет',
            3 => 'Бюджетная основа'
        ];
        $header_data = ListCompetitionHeader::find()->select(['institute', 'speciality'])->asArray()->all();
        $institutes_array = [];
        $speciality_array = [];
        foreach ($header_data as $header) {
            $institutes_array[$header['institute']] = $header['institute'];
            $speciality_array[$header['speciality']] = $header['speciality'];
        }
        $institutes = array_unique($institutes_array);
        $specs = array_unique($speciality_array);
        $fios_data = ListCompetitionRow::find()->select(['user_guid', 'fio'])->asArray()->all();

        $fios_array = [];
        foreach ($fios_data as $fios) {
            if (!array_key_exists($fios['user_guid'], $fios_array)) {
                $fios_array[$fios['user_guid']] = $fios['fio'];
            }

        }
        $fios = $fios_array;

        return $this->renderPartial('competition', [
            'qualifications' => $qualifications,
            'learnForms' => $learnForms,
            'finance_forms' => $finance_forms,
            'institutes' => $institutes,
            'specs' => $specs,
            'fios' => $fios,
        ]);
    }

    public function actionChance()
    {
        $data = Yii::$app->getModule('student')->getModule('admission')->admissionLoader->loadChance();
        if (Yii::$app->request->isPost) {
            $data = Yii::$app->getModule('student')->getModule('admission')->admissionLoader->loadChance();
            return $this->renderPartial('_chance', ['data' => $data]);
        }
        return $this->renderPartial('chance', ['data' => $data]);
    }

    public function actionTotallist()
    {
        return $this->render('total_view');
    }

    public function actionCompetitionlist()
    {
        return $this->render('competition_view');
    }

    public function actionSpecialitylist()
    {
        return $this->render('speciality_view');
    }

    public function actionChancelist()
    {
        return $this->render('chance_view');
    }

    public function actionShowchancelist($id, $specid)
    {
        $chance = \common\modules\abiturient\modules\admission\models\ListChanceHeader::findOne($id);
        $spec = BachelorSpeciality::findOne($specid);
        if ($spec == null) {
            return $this->redirect('/abiturient/applications', 302);
        }
        $user = $spec->application->user;
        $row = \common\modules\abiturient\modules\admission\models\ListChanceRow::findOne(['user_guid' => $user->guid]);

        $code = null;
        if ($row != null) {
            $code = $row->user_guid;
        }

        if (Yii::$app->request->isPost) {
            return $this->renderPartial('_chance', ['data' => $chance, 'code' => $code]);
        }
        return $this->render('chance', ['data' => $chance, 'code' => $code]);
    }

    public function actionShowlist($id)
    {
        $fio = "";
        if (!Yii::$app->request->isPost) {
            $spec = BachelorSpeciality::findOne($id);
            if ($spec == null) {
                return $this->redirect('/abiturient/applications', 302);
            }
            $user = $spec->application->user;
            $row = ListCompetitionRow::findOne(['user_guid' => $user->guid]);
            $fio = $row->user_guid;
            $head = \common\modules\abiturient\modules\admission\models\ListCompetitionHeader::findOne([
                'campaign_code' => $spec->application->type->campaign->referenceType->reference_id,
                'speciality_system_code' => $spec->speciality->speciality_code,
                'finance_code' => $spec->speciality->finance_code,
                'learnform_code' => $spec->speciality->eduform_code,
            ]);
            $rows = $head->rows;
        }

        $qualifications = [
            0 => 'Специалист/бакалавр',
            1 => 'Магистр',
            2 => 'Аспирант'
        ];
        $learnForms = [
            1 => 'Очная',
            2 => 'Очно-заочная',
            3 => 'Заочная'
        ];
        $finance_forms = [
            1 => 'Целевой приём',
            2 => 'Внебюджет',
            3 => 'Бюджетная основа'
        ];
        $header_data = ListCompetitionHeader::find()->select(['institute', 'speciality'])->asArray()->all();
        $institutes_array = [];
        $speciality_array = [];
        foreach ($header_data as $header) {
            $institutes_array[$header['institute']] = $header['institute'];
            $speciality_array[$header['speciality']] = $header['speciality'];
        }
        $institutes = array_unique($institutes_array);
        $specs = array_unique($speciality_array);
        $fios_data = ListCompetitionRow::find()->select(['user_guid', 'fio'])->asArray()->all();
        $fios_array = [];
        foreach ($fios_data as $fios) {
            if (!array_key_exists($fios['user_guid'], $fios_array)) {
                $fios_array[$fios['user_guid']] = $fios['fio'];
            }

        }
        $fios = $fios_array;

        return $this->render('competition', [
            'qualifications' => $qualifications,
            'learnForms' => $learnForms,
            'finance_forms' => $finance_forms,
            'institutes' => $institutes,
            'specs' => $specs,
            'fios' => $fios,
            'fio' => $fio,
            'head' => $head,
        ]);
    }
}