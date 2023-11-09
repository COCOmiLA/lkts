<?php

namespace common\modules\student\components\chart\controllers;

use common\models\User;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;

class ChartController extends \yii\web\Controller
{
    public $role;

    public function beforeAction($action)
    {
        if (isset(Yii::$app->user->identity) && Yii::$app->user->identity->isInRole(\common\models\User::ROLE_STUDENT)) {
            $this->role = \common\models\User::ROLE_STUDENT;
        } elseif (isset(Yii::$app->user->identity) && Yii::$app->user->identity->isInRole(\common\models\User::ROLE_TEACHER)) {
            $this->role = \common\models\User::ROLE_TEACHER;
        }
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $recordbooks = [];
        $recordbook_id = null;
        if (Yii::$app->request->isPost) {
            $buffer = Yii::$app->request->post('recordbook_id');
            if (isset($buffer)) {
                $recordbook_id = $buffer;
            }
        }
        $portfolioLoader = Yii::$app->getModule('student')->portfolioLoader;
        if ($this->role == User::ROLE_STUDENT) {
            $recordbooks = $portfolioLoader->loadRecordbooks();
            if (!is_array($recordbooks)) {
                $recordbooks = [$recordbooks];
            }
            if (!empty($recordbooks) && empty($recordbook_id)) {
                $recordbook_id = $recordbooks[0]->RecordbookId;
            }
        }

        $userId = ArrayHelper::getValue(Yii::$app->user->identity, 'userRef.reference_id');
        $result = Yii::$app->soapClientStudent->load(
            'GetCurriculumPerfomance',
            [
                'UserId' => $userId,
                'RecordbookId' => $recordbook_id,
            ]
        );

        $hasError = $this->errorManager($result, $userId, $recordbook_id);
        if (!$hasError) {
            $convertedResponse = $this->convertSoapResponse($result);
            return $this->render(
                '@common/modules/student/components/chart/views/chart',
                [
                    'hasError' => $hasError,
                    'recordbooks' => $recordbooks,
                    'recordbook_id' => $recordbook_id,
                    'length' => count($convertedResponse['fullDisciplineArray']),
                    'array' => $this->makeChartSet($convertedResponse['competencyArray'], $convertedResponse['fullDisciplineArray'], 'Результаты освоения основной образовательной программы')
                ]
            );
        } else {
            return $this->render(
                '@common/modules/student/components/chart/views/chart',
                [
                    'hasError' => $hasError,
                    'recordbooks' => $recordbooks,
                    'recordbook_id' => $recordbook_id
                ]
            );
        }
    }

    








    private function errorManager($response, $userId, $recordbook_id)
    {
        if (isset($response->return->Error) && count($response->return->Error) > 0) {
            $errorArray = [];
            $message = '<strong>Ошибка при выполнении метода GetCurriculumPerfomance: </strong><ul id="chartListError">';
            foreach ($response->return->Error as $error) {
                $message .= "<li>{$error->Description}</li>";
                $errorArray[] = ['code' => $error->Code, 'message' => $error->Description];
            }
            $message .= '</ul>';
            $message = str_replace('.', '. ', $message); 
            \Yii::$app->session->setFlash('chartErrorFrom1C', $message);

            Yii::error("Ошибка выполнения метода GetCurriculumPerfomance ('UserId' = {$userId}, 'RecordbookId = {$recordbook_id})" . PHP_EOL . print_r($errorArray, true));

            return true;
        }
        return false;
    }

    






    public function convertSoapResponse($response)
    {
        $competencyArray = [];
        $fullDisciplineArray = [];
        if (isset($response, $response->return->Competencies)) {
            if (!is_array($response->return->Competencies)) {
                $response->return->Competencies = [$response->return->Competencies];
            }
            foreach ($response->return->Competencies as $competency) {
                if (!is_array($competency->Subjects->SubjectCompetenceInfo)) {
                    $competency->Subjects->SubjectCompetenceInfo = [$competency->Subjects->SubjectCompetenceInfo];
                }
                $competencyArray[$competency->CompetenceCode] = [];
                foreach ($competency->Subjects->SubjectCompetenceInfo as $discipline) {
                    $name = $discipline->Subject->ReferenceName;
                    if ($discipline->Completed == 'true') {
                        $name .= '+';
                    } else {
                        $name .= '-';
                    }
                    $competencyArray[$competency->CompetenceCode][] = $name;
                    if (!in_array($name, $fullDisciplineArray)) {
                        if ($discipline->Completed == 'true') {
                            array_unshift($fullDisciplineArray, $name);
                        } else {
                            $fullDisciplineArray[] = $name;
                        }
                    }
                }
            }
        }
        return ['competencyArray' => $competencyArray, 'fullDisciplineArray' => $fullDisciplineArray];
    }

    






















    public function makeChartSet(
        $competencyArray = [],
        $fullDisciplineArray = [],
        $xAxisLabel = '',
        $yAxisLabel = ''
    ): array {
        $labels = [];
        $datasets = [];

        $colorMap = $this->colorGenerator($fullDisciplineArray);

        if (count($competencyArray) > 0) {
            $labels = array_keys($competencyArray);

            foreach ($fullDisciplineArray as $i => $discipline) {
                $datasets[$i] = [];
                $datasets[$i]['data'] = [];
                $datasets[$i]['borderWidth'] = 3;
                $datasets[$i]['borderSkipped'] = false;
                $datasets[$i]['backgroundColor'] = $this->chooseColor($discipline, $colorMap[$discipline]);
                $datasets[$i]['borderColor'] = $colorMap[$discipline];
                $datasets[$i]['label'] = substr($discipline, 0, -1);
                foreach ($competencyArray as $competency) {
                    if (in_array($discipline, $competency)) {
                        $datasets[$i]['data'][] = 100 / count($competency); 
                    } else {
                        $datasets[$i]['data'][] = null;
                    }
                }
            }
        }

        return [
            'type' => 'bar',
            'options' => ['id' => 'chart_canvas'],
            'clientOptions' => [
                'responsive' => true,
                'maintainAspectRatio' => true,
                'aspectRatio' => 1.85,
                'scales' => [
                    'xAxes' => [[
                        'stacked' => true,
                        'scaleLabel' => [
                            'display' =>    true,
                            'labelString' => $xAxisLabel
                        ]
                    ]],
                    'yAxes' => [[
                        'id' => 'chart_yAxes',
                        'stacked' => true,
                        'scaleLabel' => [
                            'display' => false,
                            'labelString' => $yAxisLabel
                        ],
                        'ticks' => [
                            'callback' => new JsExpression('function(value) {return value + " %";}'),
                            'max' => '100',
                            'min' => '0'
                        ]
                    ]]
                ],
                'legend' => [
                    
                    
                    
                    'display' => false,
                ],
                'tooltips' => [
                    'callbacks' => [
                        'label' => new JsExpression('
                            function(tooltipItem, data) {
                                var label = data.datasets[tooltipItem.datasetIndex].label || "";
                                if (label) {
                                    label += ": ";
                                }
                                label += Math.round(tooltipItem.yLabel * 100) / 100 + " %";
                                return label;
                            }
                        ')
                    ]
                ]
            ],
            'data' => [
                'labels' => $labels,
                'datasets' => $datasets
            ]
        ];
    }

    








    private function colorGenerator($wordsArray, $start = 250, $finish = 800)
    {
        $defaultBackgroundColor = ['#024bfd', '#5bb864', '#abc632', '#eb2db8', '#8f70d9', '#0ef7c9', '#a7b149', '#cb6645', '#5813de', '#287c83', '#24e50a', '#b74b3d', '#ad348b', '#1494f1', '#7dd176', '#e3d827', '#d03fc0', '#805ecf', '#07f782', '#958936', '#cc3033', '#331dcc', '#297695', '#65f706', '#c45e6d', '#c03dbe', '#27d0f1', '#76da89', '#f3b014', '#9f3ead', '#534bc0', '#0de53f', '#837c26', '#de0f3d', '#454fc2', '#3f9da7', '#acf60e', '#dd708f', '#d12df0', '#39c6c3', '#64c46e', '#fd6a02'];
        $H = 0;
        $colorMap = [];
        $delta = ($finish - $start) / abs(count($wordsArray) - count($defaultBackgroundColor));
        for ($i = 0; $i < count($wordsArray); $i++) {
            if ($i < count($defaultBackgroundColor)) {
                $color = $defaultBackgroundColor[$i];
            } else {
                $color = $this->hslToHex([$H, 0.5, $start / 1000]);
                $start += $delta;
                $H += 0.618033988749895;
                $H = $H > 1 ? $H - 1 : $H;
            }
            $colorMap[$wordsArray[$i]] = $color;
        }
        return $colorMap;
    }

    







    private function chooseColor($choose = '', $color = '')
    {
        if (substr($choose, -1) == '+') {
            return $color;
        }
        return '#fff';
    }

    






    private function hslToHex($hsl)
    {
        list($h, $s, $l) = $hsl;

        if ($s == 0) {
            $r = $g = $b = 1;
        } else {
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;

            $g = $this->hue2rgb($p, $q, $h);
            $r = $this->hue2rgb($p, $q, $h + 1 / 3);
            $b = $this->hue2rgb($p, $q, $h - 1 / 3);
        }
        return "#{$this->rgb2hex($r)}{$this->rgb2hex($g)}{$this->rgb2hex($b)}";
    }

    


    private function hue2rgb($p, $q, $t)
    {
        if ($t < 0) {
            $t += 1;
        }
        if ($t > 1) {
            $t -= 1;
        }
        if ($t < 1 / 6) {
            return $p + ($q - $p) * 6 * $t;
        }
        if ($t < 1 / 2) {
            return $q;
        }
        if ($t < 2 / 3) {
            return $p + ($q - $p) * (2 / 3 - $t) * 6;
        }
        return $p;
    }

    


    private function rgb2hex($rgb)
    {
        return str_pad(dechex($rgb * 255), 2, '0', STR_PAD_LEFT);
    }
}
