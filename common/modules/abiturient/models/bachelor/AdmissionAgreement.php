<?php

namespace common\modules\abiturient\models\bachelor;

use backend\models\UploadableFileTrait;
use common\components\AfterValidateHandler\LoggingAfterValidateHandler;
use common\components\changeHistoryHandler\interfaces\ChangeHistoryHandlerInterface;
use common\components\CodeSettingsManager\CodeSettingsManager;
use common\components\FilesWorker\FilesWorker;
use common\components\ini\iniGet;
use common\models\errors\RecordNotValid;
use common\models\interfaces\ArchiveModelInterface;
use common\models\interfaces\FileToSendInterface;
use common\models\relation_presenters\comparison\interfaces\ICanGivePropsToCompare;
use common\models\relation_presenters\comparison\interfaces\IHaveIdentityProp;
use common\models\relation_presenters\OneToManyRelationPresenter;
use common\models\SendingFile;
use common\models\traits\ArchiveTrait;
use common\models\User;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryClasses;
use common\modules\abiturient\models\bachelor\changeHistory\interfaces\ChangeLoggedModelInterface;
use common\modules\abiturient\models\drafts\IHasRelations;
use common\modules\abiturient\models\interfaces\ICanGetPathToStoreFile;
use common\modules\abiturient\models\interfaces\IHaveCallbackAfterDraftCopy;
use Yii;
use yii\base\UserException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;












class AdmissionAgreement extends ActiveRecord
    implements
    FileToSendInterface,
    ChangeLoggedModelInterface,
    ArchiveModelInterface,
    IHasRelations,
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

    public const SCENARIO_NEW_AGREEMENT = 'insert';
    public const SCENARIO_RECOVER = 'recover';

    public $file;

    const DOCUMENT_TYPE_PREDEFINED_DATA_NAME = 'СогласиеНаЗачисление';

    public static function tableName()
    {
        return '{{%admission_agreement}}';
    }

    public static function getFileRelationTable()
    {
        return '{{%agreements_files}}';
    }

    public static function getFileRelationColumn()
    {
        return 'agreement_id';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    


    public function rules()
    {
        return [
            [['archived_at'], 'integer'],
            [
                [
                    'sent_at',
                    'speciality_id',
                ],
                'integer'
            ],
            [
                ['speciality_id'],
                'required'
            ],
            [
                ['speciality_id'],
                'validateUniqueSpec',
                'on' => self::SCENARIO_NEW_AGREEMENT
            ],
            [
                'status',
                'default',
                'value' => self::STATUS_NOTVERIFIED
            ],
            [['archive'], 'boolean'],
            [
                'status',
                'in',
                'range' => [
                    AdmissionAgreement::STATUS_NOTVERIFIED,
                    AdmissionAgreement::STATUS_VERIFIED,
                    AdmissionAgreement::STATUS_MARKED_TO_DELETE,
                ]
            ],
            [
                ['file'],
                'file',
                'extensions' => AdmissionAgreement::getExtensionsListForRules(),
                'skipOnEmpty' => true,
                'maxSize' => iniGet::getUploadMaxFilesize(false),
                'except' => [static::SCENARIO_RECOVER]
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

    public function validateUniqueSpec()
    {
        if ($this->status === self::STATUS_MARKED_TO_DELETE) {
            return true;
        }
        if (AdmissionAgreement::find()
            ->active()
            ->andWhere([AdmissionAgreement::tableName() . '.speciality_id' => $this->speciality_id])
            ->andWhere(['not', [AdmissionAgreement::tableName() . '.status' => self::STATUS_MARKED_TO_DELETE]])
            ->exists()
        ) {
            $this->addError(
                'speciality_id',
                Yii::t(
                    'abiturient/bachelor/application/admission-agreement',
                    'Подсказка с ошибкой для поля "speciality_id"; формы согласия: `Невозможно прикрепить несколько согласий на зачисление на одно направление подготовки.`'
                )
            );
            return false;
        }
        return true;
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_NEW_AGREEMENT] = $scenarios[self::SCENARIO_DEFAULT]; 
        return $scenarios;
    }

    public function getStatusString()
    {
        switch ($this->status) {
            case self::STATUS_NOTVERIFIED:
                return Yii::t('abiturient/bachelor/application/admission-agreement', 'Статус согласия: `Не подтверждено`');
            case self::STATUS_VERIFIED:
                return Yii::t('abiturient/bachelor/application/admission-agreement', 'Статус согласия: `Подтверждено`');
            case self::STATUS_MARKED_TO_DELETE:
                return Yii::t('abiturient/bachelor/application/admission-agreement', 'Статус согласия: `Отклонено`');
            default:
                return Yii::t('abiturient/bachelor/application/admission-agreement', 'Статус согласия: `Неизвестно`');
        }
    }

    


    public function attributeLabels()
    {
        return [
            'file' => Yii::t('abiturient/bachelor/application/admission-agreement', 'Подпись для поля "file"; формы согласия: `файл`'),
            'status' => Yii::t('abiturient/bachelor/application/admission-agreement', 'Подпись для поля "status"; формы согласия: `Статус`'),
            'statusString' => Yii::t('abiturient/bachelor/application/admission-agreement', 'Подпись для поля "status"; формы согласия: `Статус`'),
            'filename' => Yii::t('abiturient/bachelor/application/admission-agreement', 'Подпись для поля "filename"; формы согласия: `Имя файла`'),
            'speciality_id' => Yii::t('abiturient/bachelor/application/admission-agreement', 'Подпись для поля "speciality_id"; формы согласия: `Направление подготовки`'),
        ];
    }

    public function getSpeciality()
    {
        return $this->hasOne(BachelorSpeciality::class, ['id' => 'speciality_id']);
    }

    public function getUser()
    {
        return ArrayHelper::getValue($this, 'speciality.application.user');
    }

    public function getRawAgreementDecline()
    {
        return $this->hasOne(AgreementDecline::class, ['agreement_id' => 'id'])
            ->orderBy([AgreementDecline::tableName() . '.archive' => SORT_ASC]);
    }

    public function getAgreementDecline()
    {
        return $this->hasOne(AgreementDecline::class, ['agreement_id' => 'id'])
            ->active()
            ->orderBy([AgreementDecline::tableName() . '.archive' => SORT_ASC]);
    }

    protected function getOwnerId()
    {
        return $this->user->id;
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

    







    public function makeDeclined()
    {
        if ($this->status === AdmissionAgreement::STATUS_NOTVERIFIED) {
            return $this->archive();
        }
        $user_id = ArrayHelper::getValue($this, 'speciality.application.user.id');
        $app_code = ArrayHelper::getValue($this, 'speciality.application_code');
        $campaign_code = ArrayHelper::getValue($this, 'speciality.application.type.campaign.referenceType.reference_id');
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (isset($user_id, $app_code, $campaign_code)) {
                $toDeleteEntity = new AdmissionAgreementToDelete([
                    'user_id' => $user_id,
                    'agreement_id' => $this->id,
                    'application_code' => $app_code,
                    'campaign_code' => $campaign_code,
                    'archive' => false
                ]);
                $result = $this->markToDelete() && $toDeleteEntity->validate();
                if ($result) {
                    $toDeleteEntity->save(false);
                    $transaction->commit();
                    return true;
                }
            }
            $transaction->rollBack();
            return false;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    



    public function markToDelete()
    {
        $this->status = self::STATUS_MARKED_TO_DELETE;
        if ($this->validate()) {
            $this->save(false);
            return true;
        }
        return false;
    }

    public function archiveAllDeclines()
    {
        if (isset($this->speciality->application_code)) {
            AdmissionAgreementToDelete::updateAll(['archive' => true], [
                'user_id' => $this->user->id,
                'id' => $this->getRawAgreementToDelete()->select('id')->column()
            ]);
            AgreementDecline::updateAll(['archive' => true], [
                'id' => $this->getAgreementDecline()->select('id')->column()
            ]);
        }
    }

    public function getRawAgreementToDelete()
    {
        return $this->hasOne(AdmissionAgreementToDelete::class, ['agreement_id' => 'id']);
    }

    public function getAgreementToDelete()
    {
        return $this->getRawAgreementToDelete()->andOnCondition([AdmissionAgreementToDelete::tableName() . '.archive' => false]);
    }

    public function getClassTypeForChangeHistory(): int
    {
        return ChangeHistoryClasses::CLASS_AGREEMENT;
    }

    public function getChangeLoggedAttributes()
    {
        return [];
    }

    public function getOldClass(): ChangeLoggedModelInterface
    {
        return $this;
    }

    public function getEntityIdentifier(): ?string
    {
        if ($this->speciality !== null) {
            return $this->speciality->getEntityIdentifier();
        }
        return '';
    }

    public function getChangeHistoryHandler(): ?ChangeHistoryHandlerInterface
    {
        return null;
    }

    public function getEntityChangeType(): int
    {
        return ChangeHistory::CHANGE_HISTORY_NEW_AGREEMENT;
    }

    public function setChangeHistoryHandler(ChangeHistoryHandlerInterface $handler)
    {
        return null;
    }

    public function beforeArchive()
    {
        if ($this->agreementDecline) {
            $this->agreementDecline->archive();
        }
    }

    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            $transaction = Yii::$app->db->beginTransaction();

            $deleteSuccess = true;
            try {
                $errorFrom = '';

                $agreementDecline = $this->getRawAgreementDecline()->all();
                if (!empty($agreementDecline)) {
                    foreach ($agreementDecline as $dataToDelete) {
                        $deleteSuccess = $dataToDelete->delete();
                        if (!$deleteSuccess) {
                            $errorFrom .= "{$this->tableName()} -> {$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                            break;
                        }
                    }
                }

                if ($deleteSuccess) {
                    $agreementToDelete = $this->getRawAgreementToDelete()->all();
                    if (!empty($agreementToDelete)) {
                        foreach ($agreementToDelete as $dataToDelete) {
                            $deleteSuccess = $dataToDelete->delete();
                            if (!$deleteSuccess) {
                                $errorFrom .= "{$this->tableName()} -> {$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                                break;
                            }
                        }
                    }
                }

                if ($deleteSuccess) {
                    $this->deleteAttachedFile();

                    $transaction->commit();
                } else {
                    Yii::error("Ошибка при удалении данных с портала. В таблице: {$errorFrom}");
                    $transaction->rollBack();

                    return false;
                }
                return true;
            } catch (\Throwable $e) {
                $transaction->rollBack();
                Yii::error("Ошибка при удалении данных с портала. " . $e->getMessage());
                return false;
            }
        } else {
            return false;
        }
    }

    public function afterValidate()
    {
        (new LoggingAfterValidateHandler())
            ->setModel($this)
            ->invoke();
    }


    public function getRelationsInfo(): array
    {
        return [
            new OneToManyRelationPresenter('agreementDeclines', [
                'parent_instance' => $this,
                'child_class' => AgreementDecline::class,
                'child_column_name' => 'agreement_id',
            ]),
            new OneToManyRelationPresenter('agreementsToDelete', [
                'parent_instance' => $this,
                'child_class' => AdmissionAgreementToDelete::class,
                'child_column_name' => 'agreement_id',
                'ignore_in_comparison' => true,
            ]),
        ];
    }

    public function getIdentityString(): string
    {
        $declined = (int)(bool)$this->agreementDecline;
        return "{$this->status}_{$declined}_{$this->filename}";
    }

    public function getPropsToCompare(): array
    {
        return [
            'statusString',
            'filename',
        ];
    }
}
