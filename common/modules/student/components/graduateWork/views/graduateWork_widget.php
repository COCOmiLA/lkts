<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

?>

<div class="plan-container">
    <div class="plan-header">
        <?= Html::beginForm(Url::toRoute(['student/graduateWork']), 'post', []); ?>
        <div class="row">
            <div class="col-12">
                <label for="record_book">Учебный план:</label>

                <?= Html::dropDownList(
                    'record_book',
                    $recordBook_id,
                    ArrayHelper::map($recordBooks, 'id', 'name'),
                    ['class' => 'form-control form-group', 'id' => 'book_id']
                ); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <?= Html::submitButton('Показать', ['class' => 'btn btn-primary']); ?>
            </div>
        </div>
        <?= Html::endForm(); ?>
    </div>

    <div class="row">
        <div class="col-12 stipends-container">
            <?php if (sizeof($themes) > 0) : ?>
                <div class="table-responsive">
                    <table class="table table-striped" style="font-size: 80%">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Период</th>
                                <th>Тип работы</th>
                                <th>Предмет</th>
                                <th>Тема</th>
                                <th>Дата приказа</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 0; $i < count($themes); $i++) : ?>
                                <?php $theme = $themes[$i]; ?>
                                <tr>
                                    <td><?= ($i + 1) ?></td>
                                    <td><?= $theme->termRef->referenceName ?></td>
                                    <td><?= $theme->typeOfTheControlRef->referenceName ?></td>
                                    <td><?= $theme->subjectRef->referenceName ?></td>
                                    <td><?= $theme->theme ?></td>
                                    <td><?= date("d.m.Y", strtotime($theme->orderDate)) ?></td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <p>Данные не найдены</p>
            <?php endif; ?>
        </div>
    </div>
</div>