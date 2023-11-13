<?php

use kartik\spinner\Spinner;
use yii\bootstrap4\Modal;

Modal::begin([
    'title' => Yii::t(
        'abiturient/change-history',
        'Заголовок модального окна истории изменений: `История изменений`'
    ),
    'id' => 'changeHistoryModal',
    'class' => 'modal',
]); ?>

<?= $this->render('_changeHistoryModalFilterForm'); ?>

<div class="modal-change-body min-height-modal">
    <div class="row">
        <div class="col-12">
            <div id="modal-change-history-content-wrapper" class="change-content-wrapper">
                <div id="modal-change-history-content"></div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div id="modal-change-history-loader" class="loader-wrapper">
                <div class="loader">
                    <?= Spinner::widget([
                        'preset' => 'medium',
                        'align' => 'center',
                        'color' => 'blue',
                        'options' => ['style' => 'margin:auto']
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php Modal::end(); ?>

<style>
    .modal-change-body {
        position: relative;
        padding-bottom: 20px;
    }

    .loader-wrapper {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        background: white;
    }

    #changeHistoryModal>.modal-dialog {
        -webkit-transition: width ease-in-out 0.5s, height ease-in 0.5s;
        -o-transition: width ease-in-out 0.5s, height ease-in 0.5s;
        transition: width ease-in-out 0.5s, height ease-in 0.5s;
    }

    .min-height-modal {
        min-height: 100px;
    }

    .loader {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        height: 100%;
    }
</style>