<?php





use backend\assets\IntegrationsAsset;
use yii\helpers\Url;

IntegrationsAsset::register($this);

$this->title = 'Интеграция сервисов';
?>
<div class="row card-body">
    <div class="col-12" id="service_integration">
        <integrations-component
                :sms-deliverers='<?= json_encode($smsDeliverers) ?>'
                :integration-settings='<?= json_encode($integration_settings) ?>'
                :telegram-settings='<?= json_encode($telegram_settings) ?>'
                save-settings-url='<?= Url::to(['/integrations/save-settings']) ?>'
        >
        </integrations-component>
    </div>
</div>
