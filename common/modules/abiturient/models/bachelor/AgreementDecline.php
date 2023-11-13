<?php

namespace common\modules\abiturient\models\bachelor;

use backend\models\UploadableFileTrait;
use common\components\changeHistoryHandler\interfaces\ChangeHistoryHandlerInterface;
use common\components\CodeSettingsManager\CodeSettingsManager;
use common\components\FilesWorker\FilesWorker;
use common\components\ini\iniGet;
use common\models\dictionary\DocumentType;
use common\models\interfaces\ArchiveModelInterface;
use common\models\interfaces\FileToSendInterface;
use common\models\relation_presenters\comparison\interfaces\ICanGivePropsToCompare;
use common\models\relation_presenters\comparison\interfaces\IHaveIdentityProp;
use common\models\SendingFile;
use common\models\traits\ArchiveTrait;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryClasses;
use common\modules\abiturient\models\bachelor\changeHistory\interfaces\ChangeLoggedModelInterface;
use common\modules\abiturient\models\interfaces\ICanGetPathToStoreFile;
use common\modules\abiturient\models\interfaces\IHaveCallbackAfterDraftCopy;
use Yii;
use yii\base\UserException;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;














class AgreementDecline extends \yii\db\ActiveRecord
    implements
    FileToSendInterface,
    ChangeLoggedModelInterface,
    ArchiveModelInterface,
    IHaveIdentityProp,
    ICanGetPathToStoreFile,
    ICanGivePropsToCompare,
    IHaveCallbackAfterDraftCopy
{
    use ArchiveTrait;
    use UploadableFileTrait;

    const STATUS_NOTVERIFIED = 0;
    const STATUS_VERIFIED = 1;
    const STATUS_MARKED_TO_DELETE = 2;
    public $file;

    public static function tableName()
    {
        return '{{%agreement_decline}}';
    }

    public static function getFileRelationTable()
    {
        return '{{%agreement_declines_files}}';
    }

    public static function getFileRelationColumn()
    {
        return 'agreement_decline_id';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            $this->deleteAttachedFile();
            return true;
        }
        return false;
    }

    


    public function rules()
    {
        return [
            [['archived_at'], 'integer'],
            [
                [
                    'sent_at',
                    'agreement_id',
                ],
                'integer'
            ],
            [
                [
                    'file',
                    'agreement_id',
                ],
                'required'
            ],
            [
                ['archive'],
                'boolean'
            ],
            [
                ['file'],
                'file',
                'extensions' => static::getExtensionsListForRules(),
                'skipOnEmpty' => true,
                'maxSize' => iniGet::getUploadMaxFilesize(false),
            ],
        ];
    }


    




    public static function getExtensionsListForRules(): string
    {
        return implode(', ', static::getExtensionsList());
    }

    




    public static function getExtensionsList(): array
    {
        return FilesWorker::getAllowedExtensionsToUploadList();
    }

    public function getAgreement()
    {
        return $this->hasOne(AdmissionAgreement::class, ['id' => 'agreement_id']);
    }

    protected function getOwnerId()
    {
        return isset($this->agreement->user) ? $this->agreement->user->id : $this->agreementToDelete->user_id;
    }

    public function checkAccess($user)
    {
        if ($user->isModer()) {
            return true;
        } elseif ($user->id == $this->getOwnerId()) {
            return true;
        } else {
            return false;
        }
    }

    public function getAgreementToDelete()
    {
        return $this->hasOne(AdmissionAgreementToDelete::class, ['agreement_id' => 'id'])
            ->viaTable('{{%admission_agreement}}', ['id' => 'agreement_id']);
    }

    public function getClassTypeForChangeHistory(): int
    {
        return ChangeHistoryClasses::CLASS_AGREEMENT_DECLINE;
    }

    public function getChangeLoggedAttributes()
    {
        return [];
    }

    public function getOldClass(): ChangeLoggedModelInterface
    {
        return $this;
    }

    public function getEntityChangeType(): int
    {
        return ChangeHistory::CHANGE_HISTORY_AGREEMENT_DECLINE;
    }

    public function getEntityIdentifier(): ?string
    {
        if ($this->agreement->speciality !== null) {
            return $this->agreement->speciality->getEntityIdentifier();
        }
        return '';
    }

    public function getChangeHistoryHandler(): ?ChangeHistoryHandlerInterface
    {
        return null;
    }

    public function setChangeHistoryHandler(ChangeHistoryHandlerInterface $handler)
    {
        return null;
    }

    public function getIdentityString(): string
    {
        return $this->filename;
    }

    public function getPropsToCompare(): array
    {
        return ArrayHelper::merge(
            array_keys($this->attributes),
            [
                'filename',
            ]
        );
    }

    public function getIsSentTo1C(): bool
    {
        return !empty($this->sent_at);
    }
}
