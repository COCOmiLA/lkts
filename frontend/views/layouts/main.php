<?php

use yii\bootstrap4\Alert;
use yii\helpers\ArrayHelper;
use yii\web\View;







$this->beginContent('@frontend/views/layouts/base.php')

?>

    <div id="wrapper">
        <div class="content" id="content-main">
            <div class="container">
                <div class="site-index content">
                    <div class="body-content">
                        <?php if (Yii::$app->session->hasFlash('alert') && ArrayHelper::getValue(Yii::$app->session->getFlash('alert'), 'body')) : ?>
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
            <div class="col-12">
                <p>
                    <?= Yii::$app->configurationManager->getText('footer_info'); ?>
                </p>
            </div>
        </div>
    </footer>

<?php $this->endContent();