<?php

use common\modules\abiturient\models\bachelor\BachelorApplication;
use yii\bootstrap4\Html;
use yii\helpers\Url;
use yii\web\View;








$panelType = $application->isArchive() ? 'danger' : 'default';
$panelType = $application->id == $id_application_came_from ? 'success' : $panelType;

$divider = 2;
if (!$application->archive_reason) {
    $divider += 3;
}

?>

<div class="card mb-3 panel-<?php echo $panelType ?>">
    <div class="card-body">
        <div class="equal row">
            <div class="col-sm-11 col-12">
                <div class="row ">
                    <div class="word-breaker col-4">
                        <?php $dateList = [
                            'created_at',
                            'sent_at',
                            'approved_at',
                        ];
                        $elements = [];
                        foreach ($dateList as $date) {
                            if (!$application->{$date}) {
                                continue;
                            }
                            $label = Html::tag('strong', $application->getAttributeLabel($date) . ':');
                            $farmsteadDate = date('d.m.Y H:i:s', $application->{$date});
                            $elements[] = "{$label} {$farmsteadDate}";
                        }

                        echo implode('<br>', $elements) ?>
                    </div>

                    <div class="word-breaker col-3">
                        <label for="status-<?php echo $application->id ?>">
                            <strong>
                                <?php echo $application->getAttributeLabel('status') ?>:
                            </strong>
                        </label>

                        <div id="status-<?php echo $application->id ?>">
                            <?php echo $application->translateStatus() ?>
                        </div>
                    </div>

                    <div class="word-breaker col-<?php echo $divider ?>">
                        <?php echo $application->translateArchiveStatus() ?>

                        <?php if ($application->archived_at) : ?>
                            <br>

                            <?php echo date('d.m.Y H:i:s', $application->archived_at) ?>
                        <?php endif; ?>
                    </div>

                    <?php if ($application->archive_reason) : ?>
                        <div class="word-breaker col-3">
                            <?php $archiveInitiator = $application->archiveInitiator; ?>
                            <?php if ($archiveInitiator) : ?>
                                <label for="archive-initiator-name-<?php echo $application->id ?>">
                                    <strong>
                                        <?php echo $application->getAttributeLabel('archiveInitiatorName') ?>:
                                    </strong>
                                </label>

                                <div id="archive-initiator-name-<?php echo $application->id ?>">
                                    <?php echo $application->archiveInitiatorName ?>
                                </div>

                                <br>
                            <?php endif; ?>

                            <?php echo $application->getArchiveReason() ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-sm-1 col-12 float-right vertical-align">
                <?php $url = Url::toRoute(['/sandbox/view', 'id' => $application->id]);
                $tooltipMessage = Yii::t(
                    'sandbox/view/accounting-benefits/target-areas',
                    'Подпись кнопки скачивания в таблице блока целевые договоры на стр. просмотра заявления: `Просмотреть`'
                );
                echo Html::a(
                    '<i class="fa fa-eye"></i>',
                    $url,
                    [
                        'class' => 'btn btn-secondary btn-sm float-right',
                        'title' => $tooltipMessage,
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'top',
                    ]
                ); ?>
            </div>
        </div>
    </div>
</div>