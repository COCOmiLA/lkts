<?php





use common\modules\abiturient\models\bachelor\CampaignInfo;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = 'Этапы приемной кампании: ' . $campaign->name;

?>

<div class="scan-update">
    <p>
        <a class='btn btn-primary' href="<?php echo Url::toRoute(['admission/index']); ?>">Назад</a>
    </p>
    <div class="alert alert-info">
        <p>
            Чтобы просмотреть даты этапов подачи согласий на зачисление, нажмите на стрелку
        </p>
    </div>
    <?php if (!empty($campaign->info)) : ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Финансирование</th>
                    <th>Форма обучения</th>
                    <th>Уровень подготовки</th>
                    <th>Категория приема</th>
                    <th>Код особой<br>группы</th>
                    <th>Дата начала приема документов</th>
                    <th>Дата окончания приема документов</th>
                    <th>Дата начала приказа</th>
                    <th>Дата окончания приказа</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($campaign->getInfo()->with(['periodsToSendAgreement', 'detailGroupRef'])
                    ->joinWith('educationSourceRef education_source_ref', false)
                    ->orderBy(['education_source_ref.reference_id' => SORT_DESC])->all() as $info) : ?>
                    <?php  ?>
                    <tr>
                        <td><?php echo $info->financeName; ?></td>
                        <td><?php echo $info->eduformName; ?></td>
                        <td><?php echo ArrayHelper::getValue($info, 'educationLevelRef.reference_name'); ?></td>
                        <td><?php echo $info->admissionCategory !== null ? $info->admissionCategory->description : 'Не указана' ?></td>
                        <td><?php echo $info->detailGroupRef->reference_id ?? ''; ?></td>
                        <td> <?php
                                echo $info->date_start;
                                ?>
                        </td>
                        <td> <?php
                                echo $info->date_final;
                                ?>
                        </td>
                        <td> <?php
                                echo $info->date_order_start;
                                ?>
                        </td>
                        <td> <?php
                            echo $info->date_order_end;
                            ?>
                        </td>
                        <td style="width: 10%;">
                            <div class="d-flex justify-content-end align-items-center">
                                <a class="needs-glyph-toggle" role="button" data-toggle="collapse" href="#info-periods-<?= $info->id ?>" aria-expanded="false" aria-controls="info-periods-<?= $info->id ?>">
                                    <i style="font-size: 150%;margin-left: 10px;padding: 10px;" class="fa fa-chevron-down" data-toggle="tooltip" data-placement="bottom" title="Этапы подачи согласий"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="8">
                            <div class="collapse" id="info-periods-<?= $info->id ?>">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Дата начала</th>
                                            <th>Дата окончания</th>
                                            <th>Только в день приёма заявления по
                                                конкурсу
                                            </th>
                                            <th>Только в день приёма первого
                                                заявления
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($info->periodsToSendAgreement)) : ?>
                                            <?php foreach ($info->periodsToSendAgreement as $index => $period) : ?>
                                                <?php  ?>
                                                <tr>
                                                    <td>
                                                        <div style="width: 10%;" class="d-flex justify-content-center align-items-center">
                                                            <strong><?php echo $index; ?></strong>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        echo $period->start;
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        echo $period->end;
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php echo Html::checkbox("PeriodToSendAgreement[{$info->id}][{$period->id}][in_day_of_sending_speciality_only]", (bool)$period->in_day_of_sending_speciality_only, ['style' => 'margin-left: 15px;', 'disabled' => true]) ?>
                                                    </td>
                                                    <td>
                                                        <?php echo Html::checkbox("PeriodToSendAgreement[{$info->id}][{$period->id}][in_day_of_sending_app_only]", (bool)$period->in_day_of_sending_app_only, ['style' => 'margin-left: 15px;', 'disabled' => true]) ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p>Этапы для данной приемной кампании не загружены</p>
    <?php endif; ?>
</div>

<?php
$script = <<<JS
    $('[data-toggle="tooltip"]').tooltip(); // добавляем в форму поля периодов

    $(document).on('click', '.needs-glyph-toggle', function () {
      var icon_elem = $(this).find('.fa');
      icon_elem.toggleClass('fa-chevron-down');
      icon_elem.toggleClass('fa-chevron-up');
    });
JS;
$this->registerJs($script);
