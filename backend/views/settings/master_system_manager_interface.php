<?php

use backend\models\MasterSystemManagerInterfaceSetting;
use yii\helpers\Html;
use yii\widgets\ActiveForm;




$this->title = 'Интерфейс модератора';

?>

<style>
    .master-system-manager-actions__ul {
        /*list-style: none;*/
    }

    .master-system-manager-actions__ul li {
        margin-bottom: 12px;
    }


    .master-system-manager-actions__wrapper {
        border-right: 1px solid grey;
    }

    .master-system-manager-settings__wrapper form {
        padding-left: 24px;
        height: 100%;
        display: -webkit-card;
        display: -ms-flexbox;
        display: flex;
        -webkit-card-orient: vertical;
        -webkit-card-direction: normal;
        -ms-flex-flow: column;
        flex-flow: column;
        -webkit-card-pack: justify;
        -ms-flex-pack: justify;
        justify-content: space-between;
    }

    .master-system-manager__wrapper {
        display: -webkit-card;
        display: -ms-flexbox;
        display: flex;
        -ms-flex-item-align: stretch;
        -ms-grid-row-align: stretch;
        align-self: stretch;
    }

    .part-wrapper {
        height: 100%;
        display: -webkit-card;
        display: -ms-flexbox;
        display: flex;
        -webkit-card-orient: vertical;
        -webkit-card-direction: normal;
        -ms-flex-flow: column;
        flex-flow: column;
    }

    .part-header {
        margin-bottom: 24px;
    }

    .part-content {
        -webkit-card-flex: 1;
        -ms-flex-positive: 1;
        flex-grow: 1;
    }
</style>

<?php
$successMessage = Yii::$app->session->getFlash('masterSystemManagerSuccessMessage');
if (!empty($successMessage)) : ?>
    <div class="alert alert-success">
        <?= $successMessage ?>
    </div>
<?php endif; ?>

<div class="row master-system-manager__wrapper">
    <?php if ($isMasterSystemManagerEnabled) : ?>
        <div class="col-6">
            <div class="master-system-manager-actions__wrapper part-wrapper">
                <div class="row part-header">
                    <div class="col-12">
                        <h4>Действия</h4>
                    </div>
                </div>
                <div class="row part-content">
                    <div class="col-12" style="height: 100%">
                        <ul class="master-system-manager-actions__ul">
                            <li>
                                <a href="update-admission-campaign-tokens" id="dictionary-button" class="btn btn-primary">Обновить токены приемных кампаний</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="col-6">
        <div class="master-system-manager-settings__wrapper part-wrapper">
            <div class="row part-header">
                <div class="col-12">
                    <h4>Интерфейс модератора 1С</h4>
                </div>
            </div>
            <div class="row part-content">
                <div class="col-12" style="height: 100%">
                    <div class="row">
                        <div class="col-12">
                            <?php $form = ActiveForm::begin([
                                'id' => 'auth-form',
                                'fieldConfig' => [
                                    'template' => "{input}\n{error}"
                                ]
                            ]); ?>
                            <?php foreach ($settings as $setting) : ?>
                                <?php if ($setting->type === 'bool') : ?>
                                    <?php echo $form->field($setting, "[$setting->id]value")->checkbox(['label' => MasterSystemManagerInterfaceSetting::GetSettingLabel($setting->name)]); ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <div class="master-system-manager-settings__form_actions">
                                <?php echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary float-right']); ?>
                            </div>
                            <?php ActiveForm::end() ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row master-system-manager__wrapper">
    <div class="col-6">
        <div class="master-system-manager-settings__wrapper part-wrapper">
            <div class="row part-header">
                <div class="col-12">
                    <h4>Интерфейс модератора портала</h4>
                </div>
            </div>
            <div class="row part-content">
                <div class="col-12" style="height: 100%">
                    <div class="row">
                        <div class="col-12">
                            <?php
                            $formPortalManager = ActiveForm::begin([
                                'id' => 'portal-manager-interface-settings-form',
                                'fieldConfig' => [
                                    'template' => "{input}\n{error}"
                                ]
                            ]); ?>
                            <?php foreach ($portalManagerSettings as $setting) : ?>
                                <?php if (in_array($setting->name, [
                                    'need_approvement_and_declination_confirm'
                                ])) : ?>
                                    <?php echo $formPortalManager->field($setting, "[$setting->id]value")->checkbox(['label' => $setting->description]); ?>
                                <?php else : ?>
                                    <label><?php echo $setting->description; ?></label>
                                    <?php echo $formPortalManager->field($setting, "[$setting->id]value"); ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <div class="master-system-manager-settings__form_actions">
                                <?php echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary float-right']); ?>
                            </div>
                            <?php ActiveForm::end() ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>