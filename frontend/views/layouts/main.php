<?php

use yii\bootstrap\Alert;
use yii\helpers\ArrayHelper;
use yii\web\View;







$this->beginContent('@frontend/views/layouts/base.php')

?>

    <div id="wrapper" class="new-style"
         style="height: 80%"
    >
        <div class="content" id="content-main">
            <div class="container"
                 style="border-radius: 50px;
        padding-bottom: 10px;
            "
            >
                <div class="site-index content"
                >
                    <div class="body-content">
                        <?php if (Yii::$app->session->hasFlash('alert')) : ?>
                            <?= Alert::widget([
                                'body' => ArrayHelper::getValue(Yii::$app->session->getFlash('alert'), 'body'),
                                'options' => ArrayHelper::getValue(Yii::$app->session->getFlash('alert'), 'options'),
                            ]) ?>
                        <?php endif; ?>

                        <?= $content ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="wrapper-inner">
            <div class="col-xs-12">

                <?php if (Yii::$app->controller->action->id === 'login'): ?>
                    <video width = "max-content" autoplay muted>
                        <source src="/Train.mp4" >
                    </video>
                <?php endif; ?>
            </div>
        </div>
    </footer>

<?php $this->endContent() ?>