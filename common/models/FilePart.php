<?php


namespace common\models;


use common\components\soapException;
use Yii;

class FilePart
{
    


    public $part_number;
    


    public $part_bin;
    


    private $file_name_from_1c;

    


    public $transfer_id;

    




    public function __construct($bin, $number, $transfer_id)
    {
        $this->part_bin = $bin;
        $this->part_number = $number;
        $this->transfer_id = $transfer_id;
    }

    



    public function sendFileTo1C(): bool
    {
        $result = false;
        try {
            $result = Yii::$app->soapClientAbit->load(
                'PutFilePart', [
                    'TransferId' => (string)$this->transfer_id,
                    'PartNumber' => (integer)$this->part_number,
                    'PartData' => (string)$this->part_bin,
                ]
            );
        } catch (\Throwable $th) {
            $log = [
                'data' => [
                    'TransferId' => (string)$this->transfer_id,
                    'PartNumber' => (integer)$this->part_number,
                    'PartData' => 'BIN TOO BIG',
                ],
            ];
            \Yii::error("Ошибка при обращении к методу PutFilePart: {$th->getMessage()} " . "\n" . print_r($log, true), 'PutFilePart');
            throw new soapException(
                'Ошибка обращения к методу.',
                '11002',
                'PutFilePart',
                $th->getMessage()
            );
        }

        if ($result === false) {
            return false;
        }

        if (!isset($result->return->PartFileName) || empty($result->return->PartFileName)) {
            $log = [
                'data' => [
                    'TransferId' => (string)$this->transfer_id,
                    'PartNumber' => (integer)$this->part_number,
                ],
                'result' => $result,
            ];
            $description = $result->return->Description ?? null;
            Yii::$app->session->setFlash('errorsSaveAttachedFile', "Ошибка при выполнении метода PutFilePart: {$description} ");
            Yii::error("Ошибка при выполнении метода SaveAttachedFile: {$description} " . PHP_EOL . print_r($log, true), 'PutFilePart');
            return false;
        }
        
        $this->file_name_from_1c = (string)$result->return->PartFileName;
        return true;
    }

    public function buildArrayTo1C(): array
    {
        return [
            'TransferId' => $this->transfer_id,
            'PartNumber' => $this->part_number,
            'PartFileName' => $this->file_name_from_1c
        ];
    }
}