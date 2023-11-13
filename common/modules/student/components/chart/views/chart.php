<?php

use common\models\User;
use common\modules\student\components\chart\assets\ChartAsset;
use dosamigos\chartjs\ChartJs;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;





ChartAsset::register($this);

$this->title = \Yii::$app->name . 'Результаты освоения программы';

?>

<div class="site-index">
    <div class="body-content">
        <h3>Результаты освоения программы</h3>
        <div class="plan-header">
            <?php echo Html::beginForm(Url::toRoute(['student/academicplan']), 'post', ['id' => '_chart_form']); ?>
            <div class="row">
                <div class="col-12">
                    <?php if (isset(Yii::$app->user->identity) && Yii::$app->user->identity->isInRole(User::ROLE_STUDENT)) {
                        echo '<label for="plan_plan">Учебный план:</label>';
                    } elseif (isset(Yii::$app->user->identity) && Yii::$app->user->identity->isInRole(User::ROLE_TEACHER)) {
                        echo '<label for="plan_plan">Место работы:</label>';
                    }

                    echo Html::tag(
                        'div',
                        Html::dropDownList(
                            'recordbook_id',
                            $recordbook_id,
                            ArrayHelper::map($recordbooks, 'RecordbookId', 'CurriculumName'),
                            [
                                'id' => 'recordbook_id',
                                'class' => 'form-control form-group'
                            ]
                        )
                    ); ?>
                </div>
                <div class="col-12">
                    <?php echo Html::button('Показать', ['class' => 'btn btn-primary', 'id' => '_chart_button']) ?>
                </div>
            </div>
            <?php echo Html::endForm(); ?>
        </div>

        <?php if (!$hasError) : ?>
            <div class="row">
                <div class="col-12">
                    <?php Pjax::begin([
                        'timeout' => false,
                        'enablePushState' => false,
                        'options' => ['id' => '_chart'],
                        'clientOptions' => ['method' => 'POST']
                    ]);

                    echo Html::beginTag('div', ['id' => 'chart_graphics']);
                        if (isset($array)) {
                            echo ChartJs::widget($array);
                            if (!empty($array['data']['datasets'])) {
                                $renderMethod = 'render';
                                if (Yii::$app->request->isAjax) {
                                    $renderMethod = 'renderAjax';
                                }
                                echo $this->{$renderMethod}('_legend', [
                                    'datasets' => $array['data']['datasets'],
                                ]);
                            }
                        } else {
                            echo Html::tag('div', 'Выберите учебный план', ['class' => 'alert alert-info', 'role' => 'alert']);
                        }
                    echo Html::endTag('div');

                    Pjax::end(); ?>

                    <div id='loading' style="text-align:center;">
                        <svg class="lds-spinner" width="42%" height="42%" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
                            <g transform="rotate(0 50 50)">
                                <rect x="47" y="24" rx="9.4" ry="4.8" width="6" height="12" fill="#919191">
                                    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.9166666666666666s" repeatCount="indefinite"></animate>
                                </rect>
                            </g>

                            <g transform="rotate(30 50 50)">
                                <rect x="47" y="24" rx="9.4" ry="4.8" width="6" height="12" fill="#919191">
                                    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.8333333333333334s" repeatCount="indefinite"></animate>
                                </rect>
                            </g>

                            <g transform="rotate(60 50 50)">
                                <rect x="47" y="24" rx="9.4" ry="4.8" width="6" height="12" fill="#919191">
                                    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.75s" repeatCount="indefinite"></animate>
                                </rect>
                            </g>

                            <g transform="rotate(90 50 50)">
                                <rect x="47" y="24" rx="9.4" ry="4.8" width="6" height="12" fill="#919191">
                                    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.6666666666666666s" repeatCount="indefinite"></animate>
                                </rect>
                            </g>

                            <g transform="rotate(120 50 50)">
                                <rect x="47" y="24" rx="9.4" ry="4.8" width="6" height="12" fill="#919191">
                                    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.5833333333333334s" repeatCount="indefinite"></animate>
                                </rect>
                            </g>

                            <g transform="rotate(150 50 50)">
                                <rect x="47" y="24" rx="9.4" ry="4.8" width="6" height="12" fill="#919191">
                                    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.5s" repeatCount="indefinite"></animate>
                                </rect>
                            </g>

                            <g transform="rotate(180 50 50)">
                                <rect x="47" y="24" rx="9.4" ry="4.8" width="6" height="12" fill="#919191">
                                    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.4166666666666667s" repeatCount="indefinite"></animate>
                                </rect>
                            </g>

                            <g transform="rotate(210 50 50)">
                                <rect x="47" y="24" rx="9.4" ry="4.8" width="6" height="12" fill="#919191">
                                    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.3333333333333333s" repeatCount="indefinite"></animate>
                                </rect>
                            </g>

                            <g transform="rotate(240 50 50)">
                                <rect x="47" y="24" rx="9.4" ry="4.8" width="6" height="12" fill="#919191">
                                    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.25s" repeatCount="indefinite"></animate>
                                </rect>
                            </g>

                            <g transform="rotate(270 50 50)">
                                <rect x="47" y="24" rx="9.4" ry="4.8" width="6" height="12" fill="#919191">
                                    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.16666666666666666s" repeatCount="indefinite"></animate>
                                </rect>
                            </g>

                            <g transform="rotate(300 50 50)">
                                <rect x="47" y="24" rx="9.4" ry="4.8" width="6" height="12" fill="#919191">
                                    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.08333333333333333s" repeatCount="indefinite"></animate>
                                </rect>
                            </g>

                            <g transform="rotate(330 50 50)">
                                <rect x="47" y="24" rx="9.4" ry="4.8" width="6" height="12" fill="#919191">
                                    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="0s" repeatCount="indefinite"></animate>
                                </rect>
                            </g>
                        </svg>
                    </div>
                </div>
            </div>
        <?php else : ?>
            <div class="row">
                <div class="col-12">
                    <?php $alert = Yii::$app->session->getFlash('chartErrorFrom1C');
                    if ($alert) {
                        echo Html::tag('div', $alert, ['class' => 'alert alert-danger', 'role' => 'alert']);
                    } ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>