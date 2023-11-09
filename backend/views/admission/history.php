<?php

use backend\models\applicationTypeHistory\ApplicationTypeHistory;
use common\modules\abiturient\models\bachelor\CampaignInfo;
use yii\bootstrap4\Accordion;
use yii\web\View;








$this->title = "История изменений: '{$campaign->name}'";

?>

<?php if ($histories) : ?>
    <?php foreach ($histories as $history) : ?>
        <?php  ?>
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-md-2">
                        <?php echo date('d.m.Y H:i:s', $history->created_at) ?>
                    </div>

                    <div class="col-12 col-md-4">
                        <?php echo "{$history->initiator->username} ({$history->initiator->email})" ?>
                    </div>

                    <div class="col-12 col-md-6">
                        <strong>Действие:</strong>
                        <?php echo "{$history->translateCause()}" ?>
                    </div>
                </div>

                <?php if ($history->hasApplicationTypeHistoryEntityClassInputs()) : ?>
                    <div class="row">
                        <div class="col-12">
                            <?php echo Accordion::widget([
                                'encodeLabels' => false,
                                'items' => [
                                    [
                                        'label' => 'Подробнее',
                                        'content' => $this->render(
                                            '_historyEntityClassInputs',
                                            [
                                                'history' => $history,
                                                'historyEntityClassInputs' => $history->applicationTypeHistoryEntityClassInputs,
                                            ]
                                        ),
                                    ],
                                ]
                            ]) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php else : ?>
    <div class="alert alert-info">
        Нет данных
    </div>
<?php endif;
