<?php

namespace common\components\attachmentWidget;


use yii\base\UserException;
use yii\base\Widget;







class AttachmentWidget extends Widget
{
    


    public $attachmentConfigArray;

    


    public $regulationConfigArray;

    


    public $formId;

    



    public $showAttachments = true;

    



    public $showRegulations = true;

    




    public $disableFileSizeValidation = false;

    



    public $multiple = true;

    public function init()
    {
        parent::init();

        if ($this->formId == null && !$this->disableFileSizeValidation) {
            throw new UserException('Не задан ID формы.');
        }

        if (empty($this->attachmentConfigArray) && $this->showAttachments) {
            throw new UserException('Нет конфигурации для модуля прикрепляемых файлов');
        }

        if (empty($this->regulationConfigArray) && $this->showRegulations) {
            throw new UserException('Нет конфигурации для модуля прикрепляемых файлов нормативных документов');
        }
    }

    public function run()
    {
        return $this->render('@abiturient/views/partial/fileComponent/fileComponent', [
            'showAttachments' => $this->showAttachments,
            'showRegulations' => $this->showRegulations,
            'disableFileSizeValidation' => $this->disableFileSizeValidation,
            'formId' => $this->formId,
            'regulationConfig' => $this->regulationConfigArray,
            'attachmentConfig' => $this->attachmentConfigArray,
            'multiple' => $this->multiple
        ]);
    }
}