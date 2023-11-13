<?php

namespace common\modules\abiturient\models;

use backend\models\Consent;
use backend\models\DocumentTemplate;
use common\components\filesystem\FilterFilename;
use common\models\Attachment;
use common\models\AttachmentArchive;
use common\models\errors\RecordNotValid;
use common\models\SendingFile;
use common\models\traits\HtmlPropsEncoder;
use common\modules\abiturient\models\bachelor\AdmissionAgreement;
use common\modules\abiturient\models\bachelor\AgreementDecline;
use common\modules\abiturient\models\interfaces\IReceivedFile;
use geoffry304\enveditor\exceptions\UnableWriteToFileException;
use Yii;
use yii\base\UserException;
use yii\behaviors\TimestampBehavior;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;














class File extends \yii\db\ActiveRecord
{
    use HtmlPropsEncoder;

    public const BASE_PATH = '@storage/web/scans/';

    


    public static function tableName()
    {
        return '{{%files}}';
    }

    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    public function rules()
    {
        return [
            [[
                'content_hash',
                'upload_name',
                'real_file_name',
                'base_path',
                'uid',
            ], 'string'],
            [[
                'upload_name',
                'real_file_name',
                'base_path',
            ], 'required'],
        ];
    }

    public function getFilePath()
    {
        return FileHelper::normalizePath(Yii::getAlias($this->base_path) . DIRECTORY_SEPARATOR . $this->real_file_name);
    }

    public function getFileContent(): ?string
    {
        return file_get_contents($this->getFilePath()) ?: null;
    }

    public function getSize()
    {
        $file = $this->getFilePath();
        if (file_exists($file) && !is_dir($file)) {
            return filesize($file);
        }
        return null;
    }

    public function __set($name, $value)
    {
        if ($name == 'upload_name') {
            $value = FilterFilename::sanitize($value);
        }
        parent::__set($name, $value);
    }

    public function getExtension(): ?string
    {
        $file = $this->getFilePath();

        return pathinfo($file)['extension'];
    }

    public function calcFileHash()
    {
        $this->content_hash = FilesManager::CalculateFileHash($this);
    }

    public function beforeValidate()
    {
        if (!$this->content_hash) {
            $this->calcFileHash();
        }

        return parent::beforeValidate();
    }

    public function getLinkedAttachments()
    {
        return $this->hasMany(Attachment::class, ['id' => Attachment::getFileRelationColumn()])
            ->viaTable(Attachment::getFileRelationTable(), ['file_id' => 'id']);
    }

    public function getLinkedConsents()
    {
        return $this->hasMany(Consent::class, ['id' => Consent::getFileRelationColumn()])
            ->viaTable(Consent::getFileRelationTable(), ['file_id' => 'id']);
    }

    public function getLinkedDocumentTemplates()
    {
        return $this->hasMany(DocumentTemplate::class, ['id' => DocumentTemplate::getFileRelationColumn()])
            ->viaTable(DocumentTemplate::getFileRelationTable(), ['file_id' => 'id']);
    }

    public function getLinkedArchivedAttachments()
    {
        return $this->hasMany(AttachmentArchive::class, ['id' => AttachmentArchive::getFileRelationColumn()])
            ->viaTable(AttachmentArchive::getFileRelationTable(), ['file_id' => 'id']);
    }

    public function getLinkedAdmissionAgreements()
    {
        return $this->hasMany(AdmissionAgreement::class, ['id' => AdmissionAgreement::getFileRelationColumn()])
            ->viaTable(AdmissionAgreement::getFileRelationTable(), ['file_id' => 'id']);
    }

    public function getLinkedAgreementDeclines()
    {
        return $this->hasMany(AgreementDecline::class, ['id' => AgreementDecline::getFileRelationColumn()])
            ->viaTable(AgreementDecline::getFileRelationTable(), ['file_id' => 'id']);
    }

    public function hasLinks(): bool
    {
        return $this->getLinkedAttachments()->exists()
            || $this->getLinkedAdmissionAgreements()->exists()
            || $this->getLinkedAgreementDeclines()->exists()
            || $this->getLinkedConsents()->exists()
            || $this->getLinkedDocumentTemplates()->exists()
            || $this->getLinkedArchivedAttachments()->exists();

    }

    public function destroyIfNotUsed(): void
    {
        if (!$this->hasLinks()) {
            $this->delete();
        }
    }

    protected function isStoredFileUsedByOthers(): bool
    {
        return File::find()
            ->where(['base_path' => $this->base_path])
            ->andWhere(['real_file_name' => $this->real_file_name])
            ->andWhere(['not', ['id' => $this->id]])
            ->exists();
    }

    public function fileExists(): bool
    {
        return file_exists($this->getFilePath());
    }

    public function afterDelete()
    {
        parent::afterDelete();
        if (!$this->isStoredFileUsedByOthers()) {
            $abs_path = $this->getFilePath();
            if (file_exists($abs_path) && !is_dir($abs_path)) {
                FileHelper::unlink($abs_path);
            }
        }
    }

    protected static function makeSalt()
    {
        return substr(md5(microtime()), rand(0, 26), 5);
    }

    






    public static function GetOrCreateByTempFile(string $base_path, $file): File
    {
        [$upload_name, $file_extension, $file_hash, $file_uid, $save_callback] = File::getTempFileInfos($file);
        $stored_file = FilesManager::FindFile(
            $upload_name,
            $file_hash,
            $file_uid,
            $base_path,
        );
        if (!$stored_file) {
            $stored_file = new File();
        }
        if ($stored_file->getIsNewRecord() || !$stored_file->fileExists()) {
            $salt = File::makeSalt();
            $filename = md5($upload_name . $salt) . time() . '.' . $file_extension;
            $full_path = FileHelper::normalizePath(Yii::getAlias($base_path) . '/' . $filename);
            $new_dir = pathinfo($full_path)['dirname'];
            FilesManager::EnsureDirectoryExists($new_dir);
            if (!$save_callback($full_path)) {
                throw new UserException('Не удалось сохранить файл');
            }
            $stored_file->base_path = $base_path;
            $stored_file->upload_name = $upload_name;
            $stored_file->uid = $file_uid;
            $stored_file->real_file_name = $filename;
            if (!$stored_file->save()) {
                throw new RecordNotValid($stored_file);
            }
        }
        return $stored_file;
    }

    public function getPartsCount(): int
    {
        return intval(ceil($this->size / SendingFile::getChunkSize()));
    }

    



    protected static function getTempFileInfos($file): array
    {
        if ($file instanceof UploadedFile) {
            $upload_name = $file->name;
            $extension = $file->extension;
            $hash = FilesManager::GetFileHash($file->tempName);
            $save_callback = function (string $path) use ($file): bool {
                return $file->saveAs($path);
            };

            return [$upload_name, $extension, $hash, null, $save_callback];
        } elseif ($file instanceof IReceivedFile) {
            $upload_name = $file->uploadName;
            $extension = $file->extension;
            $hash = $file->hash;
            $uid = $file->fileUID;
            $save_callback = function (string $path) use ($file): bool {
                return file_put_contents($path, $file->getFileContent()) !== false;
            };

            return [$upload_name, $extension, $hash, $uid, $save_callback];
        } elseif (is_array($file)) {
            return $file;
        }
        throw new UserException('Неизвестный тип объекта');
    }

    public function sameAs(?File $other): bool
    {
        if (!$other) {
            return false;
        }
        if ($this->id == $other->id) {
            return true;
        }

        return $this->upload_name == $other->upload_name
            && $this->content_hash == $other->content_hash
            && $this->uid == $other->uid
            && $this->base_path == $other->base_path;
    }

    public function getCopy(string $new_base_path, ?string $new_uid): File
    {
        if ($this->base_path == $new_base_path && $new_uid == $this->uid) {
            return $this;
        }

        $stored_file = FilesManager::FindFile(
            $this->upload_name,
            $this->content_hash,
            $new_uid,
            $new_base_path
        );

        $file_found = boolval($stored_file);
        $file_exists_in_found = $file_found && $stored_file->fileExists();
        if (!($file_found && $file_exists_in_found)) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if (!$file_found) {
                    $stored_file = new File();
                    $stored_file->base_path = $new_base_path;
                    $stored_file->upload_name = $this->upload_name;
                    $stored_file->uid = $new_uid;
                    $stored_file->real_file_name = $this->real_file_name;
                    $stored_file->content_hash = $this->content_hash;
                    $stored_file->save(false);

                    $file_exists_in_found = $stored_file->fileExists();
                }
                
                if (!$file_exists_in_found) {
                    $new_dir = pathinfo($stored_file->getFilePath())['dirname'];
                    FilesManager::EnsureDirectoryExists($new_dir);

                    $result = copy(
                        $this->getFilePath(),
                        $stored_file->getFilePath()
                    );
                    if (!$result) {
                        Yii::error("Не удалось скопировать файл {$this->getFilePath()} в {$stored_file->getFilePath()}");
                        throw new UnableWriteToFileException('Не удалось скопировать файл');
                    }
                }

                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
        }
        return $stored_file;
    }
}