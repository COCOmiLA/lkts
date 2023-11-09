<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

?>

<div class="plan-container">
    <div class="plan-header">
        <?= Html::beginForm(Url::toRoute(['student/stipend']), 'post', []); ?>

        <div class="row">
            <div class="col-12">
                <label for="record_book">
                    Учебный план:
                </label>

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
            <?php if (sizeof($stipends) > 0) : ?>
                <div class="table-responsive">
                    <table class="table table-striped" style="font-size: 80%">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Дата приказа</th>
                                <th>Период</th>
                                <th>Тип выплаты</th>
                                <th>Вид приказа</th>
                                <th>Курс</th>
                                <th>Сумма</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 0; $i < count($stipends); $i++) : ?>
                                <?php $stipend = $stipends[$i]; ?>
                                <tr>
                                    <td>
                                        <?= ($i + 1) ?>
                                    </td>

                                    <td>
                                        <?= $stipend->renderOrderDate() ?>
                                    </td>

                                    <td>
                                        <?= $stipend->renderDateInterval() ?>
                                    </td>

                                    <td>
                                        <?= $stipend->calculationRef->referenceName ?>
                                    </td>

                                    <td>
                                        <?= $stipend->orderTypeRef->referenceName ?>
                                    </td>

                                    <td>
                                        <?= $stipend->courseRef->referenceName ?>
                                    </td>

                                    <td>
                                        <?= $stipend->paymentAmount ?>
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <p>
                    Данные не найдены
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>