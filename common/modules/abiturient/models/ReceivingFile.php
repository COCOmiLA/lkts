<?php

namespace common\modules\abiturient\models;

use common\models\SendingFile;
use common\modules\abiturient\models\interfaces\ICanGetPathToStoreFile;
use common\modules\abiturient\models\interfaces\IReceivedFile;
use geoffry304\enveditor\exceptions\UnableReadFileException;
use geoffry304\enveditor\exceptions\UnableWriteToFileException;
use stdClass;
use Throwable;
use Yii;
use yii\helpers\FileHelper;
use yii\web\ServerErrorHttpException;


class ReceivingFile extends \yii\base\BaseObject implements IReceivedFile
{
    const BASE_PATH = '@storage/web/tempZip';

    protected $transfer_id;
    protected $parts_count;
    protected $file_hash;
    protected $file_uid;
    protected $file_name;
    protected $temp_file_names = [];

    public function __construct(stdClass $file_description_from_1c)
    {
        parent::__construct();

        $this->transfer_id = $file_description_from_1c->TransferId ?? SendingFile::generateGuid();
        $this->parts_count = $file_description_from_1c->FilePartsCount ?? 0;
        $this->file_hash = $file_description_from_1c->FileHash;
        $this->file_uid = $file_description_from_1c->FileUID;
        $this->file_name = "{$file_description_from_1c->FileName}.{$file_description_from_1c->FileExt}";
    }

    public function setTempFileNames(array $temp_file_names)
    {
        $this->temp_file_names = $temp_file_names;
        return $this;
    }

    public function getHash(): string
    {
        return $this->file_hash;
    }

    public function getFileUID(): string
    {
        return $this->file_uid;
    }

    public function fetchFilePart(int $part_number): string
    {
        $result = Yii::$app->soapClientWebApplication->load('GetFilePart', [
            'TransferId' => $this->transfer_id,
            'PartNumber' => $part_number,
            'FileName' => pathinfo($this->uploadName)['filename'],
            'FileExt' => $this->extension,
            'FileHash' => $this->hash,
            'FileUID' => $this->fileUID,
        ]);
        $partData = '';
        if (isset($result, $result->return, $result->return->PartData)) {
            $partData = $result->return->PartData;
        }
        return ReceivingFile::StoreFileDataToTempFile($partData);
    }

    public static function StoreFileDataToTempFile(string $data): string
    {
        $temp_file = ReceivingFile::GenerateUnusedFileName();

        if (file_put_contents($temp_file, $data) === false) {
            throw new UnableWriteToFileException("Не удалось записать временный файл");
        }
        return $temp_file;
    }

    public function fetchFile()
    {
        try {
            for ($i = 1; $i <= $this->parts_count; $i++) {
                $this->temp_file_names[] = $this->fetchFilePart($i);
            }
        } catch (Throwable $throwable) {
            $this->removeTempFiles();
            throw $throwable;
        }
    }

    


    public function removeTempFiles()
    {
        if ($this->temp_file_names) {
            foreach ($this->temp_file_names as $temp_file_name) {
                try {
                    if (file_exists($temp_file_name) && !is_dir($temp_file_name)) {
                        FileHelper::unlink($temp_file_name);
                    }
                } catch (Throwable $e) {
                    Yii::error("Не удалось очистить временный файл {$temp_file_name} по причине: {$e->getMessage()}");
                    return false;
                }
            }
        }
        return true;
    }

    public static function GenerateUnusedFileName(): string
    {
        $base = Yii::getAlias(ReceivingFile::BASE_PATH);
        $iter = 0;
        do {
            if ($iter > 5) {
                throw new ServerErrorHttpException('Не удалось сформировать временный каталог');
            }
            $name = FileHelper::normalizePath($base . '/' . md5(Yii::$app->security->generateRandomString()));
            $iter++;
        } while (file_exists($name));

        return $name;
    }

    public function getFileContent(): string
    {
        $tempFileNames = $this->temp_file_names;
        if (!$tempFileNames) {
            return '';
        }

        $result = '';
        foreach ($tempFileNames as $temp_file_name) {
            $tmp = file_get_contents($temp_file_name);
            if ($tmp === false) {
                throw new UnableReadFileException('Не удалось прочитать временный файл');
            }
            $result .= $tmp;
        }
        return $result;
    }


    public function getUploadName(): string
    {
        return $this->file_name;
    }

    public function getExtension(): string
    {
        return pathinfo($this->uploadName)['extension'];
    }

    public function getFile(ICanGetPathToStoreFile $entity_to_link): File
    {
        return File::GetOrCreateByTempFile(
            $entity_to_link->getPathToStoreFiles(),
            $this
        );
    }
}
