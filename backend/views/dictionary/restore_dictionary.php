<?php







use kartik\grid\CheckboxColumn;
use kartik\grid\GridView;
use kartik\helpers\Html;
use kartik\widgets\Select2;
use yii\bootstrap4\Modal;
use yii\widgets\Pjax;

$appLanguage = Yii::$app->language;

$this->title = 'Замена ссылок на устаревшие справочные данные';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="row card-body" id="row-id">
    <div class="col-12">
        <div class="alert alert-warning">
            Данная функция рекомендуется к использованию только если вы понимаете что делаете
        </div>
        <?php Pjax::begin([
            'options' => ['id' => 'restore_dictionary_pjax'],
            'timeout' => false,
        ]); ?>
        <div style="margin-bottom: 30px; margin-top: 25px;">
            <label>Название справочника</label>
            <?php echo Select2::widget([
                'language' => $appLanguage,
                'name' => 'dict_idx',
                'model' => $model,
                'attribute' => 'dict_index',
                'data' => $dicts,
                'options' => ['placeholder' => 'Выберите справочник...'],
                'pluginOptions' => [
                    'multiple' => false,
                    'allowClear' => false,
                    'dropdownParent' => '#row-id',
                ],
                'pluginEvents' => [
                    "select2:select" => "
                        function () {
                            $.pjax.reload({
                                url: '/admin/dictionary/restore-dictionary?dict_idx=' + $(this).val(),
                                timeout: false,
                                container: '#restore_dictionary_pjax',
                            });
                        }
                    "
                ]
            ]); ?>
        </div>
        <?php if ($model->dict_index !== null) {
            echo GridView::widget([
                'hover' => true,
                'headerContainer' => ['class' => 'thead-light'],
                'tableOptions' => ['class' => 'table-sm'],
                'striped' => false,
                'summary' => false,
                'id' => 'restore_dict_grid',
                'dataProvider' => $model->getProvider(),
                'filterModel' => $model,
                'pager' => [
                    'firstPageLabel' => '<<',
                    'prevPageLabel' => '<',
                    'nextPageLabel' => '>',
                    'lastPageLabel' => '>>',
                ],
                'columns' => [
                    [
                        'attribute' => 'id',
                        'label' => 'Id',
                        'value' => function ($row) {
                            return $row['id'];
                        }
                    ],
                    [
                        'attribute' => 'actual_name',
                        'label' => 'Актуальное название',
                        'value' => function ($row) {
                            return $row['actual_name'];
                        }
                    ],
                    [
                        'attribute' => 'actual_uid',
                        'label' => 'Актуальный идентификатор',
                        'value' => function ($row) {
                            return $row['actual_uid'];
                        }
                    ],
                    [
                        'attribute' => 'actual_data_version',
                        'label' => 'Актуальная версия',
                        'value' => function ($row) {
                            return $row['actual_data_version'];
                        }
                    ],
                    [
                        'attribute' => 'old_id',
                        'label' => 'Id',
                        'value' => function ($row) {
                            return $row['old_id'];
                        }
                    ],
                    [
                        'attribute' => 'old_name',
                        'label' => 'Устаревшее название',
                        'value' => function ($row) {
                            return $row['old_name'];
                        }
                    ],
                    [
                        'attribute' => 'old_uid',
                        'label' => 'Устаревший идентификатор',
                        'value' => function ($row) {
                            return $row['old_uid'];
                        }
                    ],
                    [
                        'attribute' => 'old_data_version',
                        'label' => 'Устаревшая версия',
                        'value' => function ($row) {
                            return $row['old_data_version'];
                        }
                    ],
                    [
                        'class' => CheckboxColumn::class,
                        'checkboxOptions' => function ($row) {
                            return ['value' => $row['id']];
                        }
                    ]
                ],
            ]);
            echo Html::beginForm('', 'post', ['id' => 'restore_form']);
            echo Html::button('Восстановить', ['id' => 'restore_btn', 'class' => 'btn btn-primary']);
            echo Html::endForm();
        }

        Pjax::end(); ?>
    </div>
</div>
<?php
$script = <<<JS
$(document).on('click', '#restore_btn', function () {
    var selected_row_numbers = $('#restore_dict_grid').yiiGridView('getSelectedRows');
    var selected_rows = $('#restore_dict_grid')
        .find('tr')
        .filter(function () {
            if (!this.hasAttribute('data-key')) {
                return false;
            }
            return selected_row_numbers.includes(+$(this).attr('data-key'));
        });
    $('#restore_form').find('input[name="selection[]"]').remove();

    selected_rows.each(function () {
        var value = $(this).find('input[name="selection[]"]').val();
        $('#restore_form').append('<input type="hidden" name="selection[]" value="' + value + '">');
    });

    $('#restore_form').submit();
});

$('#warning_restore_modal').modal('show');
JS;
$this->registerJs($script);

Modal::begin([
    'id' => 'warning_restore_modal',
    'title' => '<h4>Подтвердите действие</h4>',
]); ?>
<div class="alert alert-warning card-body">
    <strong>Внимание!</strong>
    <p>Операция не обратима. Данный функционал следует использовать только если вы уверены в
        выполняемых действиях</p>
</div>
<?php Modal::end();
