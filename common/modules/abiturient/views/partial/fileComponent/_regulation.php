<?php

use common\models\UserRegulation;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\form\ActiveForm;









$key = $regulation->getIndex();
?>

<div class="row">
    <div class="col-12">
        <?php $link = '';
        if ($regulation->regulation->isFileContent()) {
            $link = Html::a($regulation->regulation->name, Url::to(['/site/download-regulation-file', 'id' => $regulation->regulation->id]), [
                'download' => true,
            ]);
        } elseif ($regulation->regulation->isHTMLContent()) {
            $link = "<a href=\"\" data-toggle=\"modal\" data-target=\"#pdModal{$regulation->regulation->id}\">{$regulation->regulation->name}</a>";
        } else {
            $link = Html::a($regulation->regulation->name, $regulation->regulation->content_link, [
                'target' => '_blank'
            ]);
        }

        echo Html::activeHiddenInput($regulation, "[$key]regulation_id");

        if ($regulation->regulation->confirm_required) {
            if (isset($form)) {
                echo $form->field($regulation, "[$key]is_confirmed", [
                    'options' => [
                        'style' => 'margin-bottom: 0'
                    ]
                ])->checkbox([
                    'id' => "Regulation$key",
                    'label' => $regulation->regulation->before_link_text . ' ' . $link,
                    'uncheckValue' => null,
                    'disabled' => $isReadonly
                ]);
            } else {
                echo Html::activeCheckbox($regulation, "[$key]is_confirmed", [
                    'id' => "Regulation$key",
                    'label' => false,
                    'options' => [
                        'style' => 'margin-bottom: 0'
                    ],
                    'disabled' => $isReadonly
                ]);
                echo "<label style='margin-left: 12px' for=\"Regulation$key\">" . $regulation->regulation->before_link_text . $link . "</label>";
            }
        } else {
            echo "<label for=\"Regulation$key\">" . $regulation->regulation->before_link_text . ' ' . $link . "</label>";
        } ?>

        <?php if ($regulation->regulation->isHTMLContent()) : ?>
            <div class="modal fade" id="pdModal<?= $regulation->regulation->id ?>" tabindex="-1" role="dialog" aria-labelledby="pdModalLabel<?= $regulation->regulation->id ?>">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">
                                <?= $regulation->regulation->name ?>
                            </h4>

                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <div class="content-html" style="margin: 20px 10px">
                                <?= $regulation->regulation->content_html ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($regulation->regulation->attachmentType !== null) : ?>
        <?php $attachment = $regulation->getAttachmentCollection();
        $hasPassedApplication = false;
        if (isset($attachment->application) && $application = $attachment->application) {
            $hasPassedApplication = $application->hasPassedApplication();
        } ?>

        <div class="col-12 mt-3">
            <?= $this->render('_attachment', [
                'attachment' => $regulation->getAttachmentCollection(),
                'isReadonly' => $isReadonly,
                'hasPassedApplication' => $hasPassedApplication,
                'performRegulation' => true,
            ]); ?>
        </div>
    <?php endif; ?>
</div>