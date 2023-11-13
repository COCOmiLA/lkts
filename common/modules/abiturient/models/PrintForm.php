<?php

namespace common\modules\abiturient\models;


use common\models\User;
use Yii;
use yii\helpers\FileHelper;

class PrintForm extends \yii\base\Model
{
    const TYPE_EXAM_DIRECTION = 0;
    const TYPE_DORMITORY_DIRECTION = 1;
    const TYPE_PERSONAL_RECEIPT = 2;

    const BASE_PATH = '@storage/web/docs';
    const DOC_MASK = '%typename%_%guid%_%application_code%.pdf';

    public $model;
    public $type;
    public $filename;
    public $typename;

    public function getTypename()
    {
        switch ($this->type) {
            case (self::TYPE_EXAM_DIRECTION):
                return 'DirectionOfEntranceExaminations';
            case (self::TYPE_DORMITORY_DIRECTION):
                return 'Hostels';
            case (self::TYPE_PERSONAL_RECEIPT):
                return 'Notes';
            default:
                return '';
        }
    }

    public function CheckFileExist()
    {
        $filename = $this->filename;
        if ($filename != null) {
            $basePath = Yii::getAlias(PrintForm::BASE_PATH);
            return file_exists(FileHelper::normalizePath("{$basePath}/{$this->filename}"));
        } else {
            return false;
        }
    }

    public function getFilename()
    {
        $filename = self::DOC_MASK;
        switch ($this->type) {
            case (self::TYPE_EXAM_DIRECTION):
                return str_replace(
                    ['%typename%', '%guid%', '%application_code%'],
                    [$this->typename, $this->model->application->user->guid, $this->model->application_code],
                    $filename
                );
            case (self::TYPE_DORMITORY_DIRECTION):
                return  str_replace(
                    ['%typename%', '%guid%', '%application_code%'],
                    [$this->typename, $this->model->application->user->guid, $this->model->register_code],
                    $filename
                );
            case (self::TYPE_PERSONAL_RECEIPT):
                return  str_replace(
                    ['%typename%', '%guid%', '%application_code%'],
                    [$this->typename, $this->model->user->guid, $this->model->type->campaign->referenceType->reference_id],
                    $filename
                );
            default:
                return null;
        }
    }

    public function getFullPath()
    {
        $basePath = Yii::getAlias(PrintForm::BASE_PATH);
        return FileHelper::normalizePath("{$basePath}/{$this->filename}");
    }

    public function checkAccess($user_id)
    {
        $user = User::findOne((int)$user_id);
        if ($user == null) {
            return false;
        }
        if ($user->isModer() || $user->isInRole(User::ROLE_ADMINISTRATOR)) {
            return true;
        }
        switch ($this->type) {
            case (self::TYPE_EXAM_DIRECTION):
                return ($this->model->application->user->id == (int)$user->id);
            case (self::TYPE_DORMITORY_DIRECTION):
                return ($this->model->application->user->id == (int)$user->id);
            case (self::TYPE_PERSONAL_RECEIPT):
                return ($this->model->user->id == (int)$user->id);
            default:
                return false;
        }
    }

    public function getTitle()
    {
        switch ($this->type) {
            case (self::TYPE_EXAM_DIRECTION):
                return 'Направление на вступительные испытания (' . $this->model->discipline->discipline_name . ')';
            case (self::TYPE_DORMITORY_DIRECTION):
                return 'Направление в общежитие (с ' . $this->model->date_start . ' по ' . $this->model->date_end . ')';
            case (self::TYPE_PERSONAL_RECEIPT):
                return 'Расписка поступающего';
            default:
                return '';
        }
    }
}
