<?php


use yii\web\View;







?>

<div class="card-group">
    <div class="card mb-3" id="custom-legend">
        <div class="card-header">
            <h4>
                <a data-toggle="collapse" href="#collapse_panel">
                    Легенда
                </a>
            </h4>
        </div>
        <div id="collapse_panel" class="panel-collapse collapse">
            <div class="card-body">
                <ul>
                    <?php foreach ($datasets as $discipline) : ?>
                        <li>
                            <?php $spanStyle = sprintf(
                                'background-color: %s; border: %dpx solid %s;',
                                $discipline['backgroundColor'],
                                $discipline['borderWidth'],
                                $discipline['borderColor']
                            ); ?>
                            <span style="<?= $spanStyle ?>"></span>
                            <?= $discipline['label'] ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>