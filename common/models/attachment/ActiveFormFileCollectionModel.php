<?php

namespace common\models\attachment;

use common\components\ini\iniGet;
use common\models\AttachmentType;
use yii\base\InvalidConfigException;






class ActiveFormFileCollectionModel extends BaseFileCollectionModel
{

    public $file;
    


    private $skipOnEmpty;
    


    public $attachmentType;
    


    private $formName = null;

    private $extensionList;

    
    private $add_required_rule = false;
    private $url_to_check_client_requirement = null;
    private $param_name_to_check_requirement = null;
    private $input_selector_for_file_requirement_check = null;


    








    public function __construct($skipOnEmpty, $extensionList, AttachmentType $type, $customFormName = null, $config = [])
    {
        parent::__construct($config);
        $this->skipOnEmpty = $skipOnEmpty;
        $this->extensionList = $extensionList;
        $this->attachmentType = $type;
        $this->setFormName($customFormName ?? parent::formName());
    }

    public function setRequiredProps(bool $may_by_required, string $url, string $param_name, string $selector)
    {
        $this->add_required_rule = $may_by_required;
        $this->url_to_check_client_requirement = $url;
        $this->param_name_to_check_requirement = $param_name;
        $this->input_selector_for_file_requirement_check = $selector;

        return $this;
    }

    public function rules()
    {
        $rules = [
            [
                ['file'],
                'file',
                'extensions' => $this->extensionList,
                'skipOnEmpty' => $this->skipOnEmpty,
                'maxSize' => iniGet::getUploadMaxFilesize(false),
                'maxFiles' => iniGet::getMaximumFileUploadsNumber()
            ],
        ];
        
        if ($this->add_required_rule) {
            $rules[] = [
                ['file',],
                'required',
                'when' => function ($model) {
                    return false;
                }, 'whenClient' => "function (attribute, value) { 
                    // если в превью уже что-то есть то не проверяем
                    if ($(attribute.input).parents('.file-input').find('.file-preview .file-preview-frame').length > 0) {
                        return false;
                    }
                    var response = false;
                    $.ajax({
                        url: '{$this->url_to_check_client_requirement}',
                        type : 'POST',
                        async : false,
                        data: {{$this->param_name_to_check_requirement}: $(attribute.input).parents('form').find('{$this->input_selector_for_file_requirement_check}').val()},
                        success: function (result) {
                            response = result
                        }
                    });
                    return response;
                 }"
            ];
        }
        return $rules;
    }

    public function attributeLabels()
    {
        return [
            'file' => 'Файл'
        ];
    }

    public function getAttachmentType()
    {
        return $this->attachmentType;
    }

    public function setAttachmentType(AttachmentType $type)
    {
        $this->attachmentType = $type;
        return $this;
    }

    


    public function setFormName($formName): void
    {
        $this->formName = $formName;
    }

    



    public function formName(): string
    {
        return $this->formName ?? parent::formName();
    }


    


    public function setSkipOnEmpty($value): void
    {
        $this->skipOnEmpty = $value;
    }

    


    public function getSkipOnEmpty(): bool
    {
        return $this->skipOnEmpty;
    }
}