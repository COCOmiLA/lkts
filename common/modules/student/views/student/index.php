<?php

use yii\helpers\Url;
use yii\web\View;









$this->title = Yii::$app->name;

$countNewRoutes = count($newRoutes);
$countLeftRoutes = count($leftRoutes);
$countRightRoutes = count($rightRoutes);

$pageDivider = '6';
if ($countLeftRoutes < 1 || $countRightRoutes < 1) {
    $pageDivider = '12';
}

$sortedElementsSids = [
    ['count' => $countLeftRoutes,  'list' => $leftRoutes],
    ['count' => $countRightRoutes, 'list' => $rightRoutes],
];

?>

<div class="site-index">
    <div class="body-content">
        <?php if ($countLeftRoutes > 0 || $countRightRoutes > 0) : ?>
            <div class="row">
                <?php foreach ($sortedElementsSids as $sortedElements) : ?>
                    <?php [
                        'list' => $list,
                        'count' => $count,
                    ] = $sortedElements; ?>
                    <?php if ($count > 0) : ?>
                        <div class="col-sm-<?= $pageDivider ?> col-12">
                            <?php foreach ($list as $routeData) : ?>
                                <?php [
                                    'url' => $route,
                                    'description' => $link_name
                                ] = $routeData; ?>
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <a href="<?= (strpos($route, 'htt') !== false) ? $route : Url::toRoute($route); ?>">
                                            <?= $link_name; ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($countNewRoutes > 0) : ?>
            <?php $i = 0; ?>
            <?php foreach ($newRoutes as $routeData) : ?>
                <?php [
                    'url' => $route,
                    'description' => $link_name
                ] = $routeData; ?>
                <?php if ($i % 2 == 0) : ?>
                    <div class="row">
                    <?php endif; ?>
                    <div class="col-sm-6 col-12">
                        <div class="card mb-3">
                            <div class="card-body">
                                <a href="<?= (strpos($route, 'htt') !== false) ? $route : Url::toRoute($route); ?>">
                                    <?= $link_name; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php if ($i % 2 !== 0 || $i == ($countNewRoutes - 1)) : ?>
                    </div>
                <?php endif; ?>
                <?php $i++; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>