<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

$links = [];
if (isset($this->params['breadcrumbs'])) {
    $links = $this->params['breadcrumbs'];
}

?>

<div class="row">
    <div class="hidden-xs col-sm-<?= isset($this->params['no-search']) && $this->params['no-search'] === true ? '12' : '9' ?>">
        <?= Breadcrumbs::widget([
            'links' => $links,
            'homeLink' => [
                'url' => Url::to('/'),
                'label' => Yii::t('yii', 'Home'),
                'template' => (new Breadcrumbs)->itemTemplate,
            ],
        ]); ?>
    </div>

    <?php if (!isset($this->params['no-search']) || $this->params['no-search'] !== true) : ?>
        <div class="col-sm-3">
            <?= Html::beginForm(['forum/search'], 'get'); ?>

            <div class="form-group">
                <div class="input-group">
                    <?= Html::textInput('query', null, ['class' => 'form-control']); ?>
                    <div class="input-group-btn">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                            <span class="glyphicon glyphicon-search"></span>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-right dropdown-menu-with-old-style" role="menu">
                            <li>
                                <a href="<?= Url::to(['forum/search']) ?>">
                                    <?= Yii::t('podium/view', 'Advanced Search Form') ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <?= Html::endForm(); ?>
        </div>
    <?php endif; ?>
</div>