<?php

use common\components\EnvironmentManager\exceptions\EnvironmentException;
use yii\helpers\Html;







$events = ['UnsuccessfulConnectionWithSoap'];

$errorArray = [];
foreach ($events as $event) {
    
    $alert = \Yii::$app->session->getFlash($event);
    if (strlen((string)$alert) > 1) {
        $errorArray[] = $alert;
    }
}

$this->title = $name;
?>
<?php if($exception instanceof EnvironmentException):?>
    <div class="error">
        <div class="row">
            <div class="col-12">
                <div class="error-content text-center d-flex" >
                    <div class="alert alert-danger" style="width: 60%; margin: auto">
                        <?= $exception->getMessage() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="error">
        <div class="row">
            <div class="col-12">
                <div class="error-content text-center">
                    <h3 class="headline">
                        <i class="fa fa-warning text-yellow"></i>
                        <?php if (count($errorArray) < 1) {
                            echo Yii::t(
                                'backend',
                                'Ошибка {code}',
                                ['code' => property_exists($exception, 'statusCode') ? $exception->statusCode : 500]
                            );
                        } ?>
                    </h3>
                    <p>
                        <?php echo nl2br(Html::encode($message)); ?>
                    </p>
                </div>
                <?php
                    echo Html::beginTag('div', ['class' => 'body-content']);
                        echo Html::beginTag('div', ['class' => 'alert alert-warning', 'role' => 'alert']);
                            echo Html::tag(
                                'div',
                                '<strong>Error ('.$exception->getFile() . ':' . $exception->getLine() .')</strong>: <br>' . nl2br(Html::encode($exception->getMessage()))  . "<br><br><strong>Error Trace:</strong><br> " . nl2br(Html::encode($exception->getTraceAsString()))
                            );
                        echo Html::endTag('div');
                    echo Html::endTag('div');
                ?>
                <?php
                if(count($errorArray)) {
                    echo Html::beginTag('div', ['class' => 'body-content']);
                    $last = count($errorArray) - 1;
                    echo Html::beginTag('div', ['class' => 'alert alert-warning', 'role' => 'alert']);
                    foreach ($errorArray as $key => $errorMessage) {
                        echo Html::tag(
                            'div',
                            $errorMessage,
                            [
                                'role' => 'alert',
                                'class' => 'alert alert-danger',
                                'id' => ($last == $key)? 'lastErrorMessage' : ''
                            ]
                        );
                    }
                    echo Html::endTag('div');
                    echo Html::endTag('div');
                }?>
            </div>
        </div>
    </div>
<?php endif;