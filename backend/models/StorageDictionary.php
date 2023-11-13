<?php

namespace backend\models;

use geoffry304\enveditor\components\EnvComponent;
use Yii;
use yii\base\Model;
use yii\helpers\FileHelper;

class StorageDictionary extends Model
{
    
    public $envEditor;

    
    public $storagePath;

    


    public function rules()
    {
        return [
            [
                'storagePath',
                'trim'
            ],
            [
                'storagePath',
                'string'
            ],
            [
                'storagePath',
                'required',
                'when' => function (StorageDictionary $model, string $attr) {
                    if (empty($model->$attr)) {
                        return false;
                    }
                    $path = FileHelper::normalizePath($model->$attr);
                    if (file_exists($path)) {
                        if (is_dir($path)) {
                            
                            $mainStoragePath = FileHelper::normalizePath(__DIR__ . '..\..\..\storage');
                            if ($mainStoragePath == $path) {
                                return true;
                            }
                            $mainDirs = FileHelper::findDirectories(
                                $mainStoragePath,
                                ['filter' => function ($path) use ($mainStoragePath) {
                                    return StorageDictionary::pathFilter($path, $mainStoragePath);
                                }]
                            );

                            foreach ($mainDirs as $mainDir) {
                                $relativePath = FileHelper::normalizePath("{$path}/" . str_replace($mainStoragePath, '', $mainDir));
                                if (!file_exists($relativePath) && !FileHelper::createDirectory($relativePath)) {
                                    $model->addError(
                                        $attr,
                                        "Не удалось создать директорию: '{$relativePath}'"
                                    );
                                    return false;
                                }
                                $filesFormMainDir = FileHelper::findFiles(
                                    $mainDir,
                                    ['filter' => function ($path) use ($mainStoragePath) {
                                        return StorageDictionary::pathFilter($path, $mainStoragePath);
                                    }]
                                );
                                if (!empty($filesFormMainDir)) {
                                    foreach ($filesFormMainDir as $filePath) {
                                        $relativeFilePath = FileHelper::normalizePath("{$path}/" . str_replace($mainStoragePath, '', $filePath));
                                        if (!copy($filePath, $relativeFilePath)) {
                                            $model->addError(
                                                $attr,
                                                "Не удалось скопировать файл из '{$filePath}' в '{$relativeFilePath}'"
                                            );
                                            return false;
                                        }
                                    }
                                }
                            }
                            
                        } elseif (!empty($path)) {
                            $model->addError(
                                $attr,
                                "Введённое значение ('$path') не является директорией"
                            );
                            return true;
                        }
                    } elseif (!empty($path)) {
                        $model->addError(
                            $attr,
                            "Указанный путь ('$path') не существует"
                        );
                        return true;
                    }
                    return false;
                },
                'whenClient' => 'function() {return false;}'
            ]
        ];
    }

    


    public function __construct(string $storagePath = '')
    {
        parent::__construct([]);

        
        $envEditorModule = Yii::$app->env;
        $this->envEditor = $envEditorModule->load(FileHelper::normalizePath('../../.env'));

        $this->storagePath = $storagePath;
    }

    


    public
    function attributeLabels()
    {
        return ['storagePath' => 'Путь до хранилища'];
    }

    




    public
    function save()
    {
        $storagePath = $this->storagePath;
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $storagePath = str_replace('\\', '\\\\', $storagePath);
        }
        $this->envEditor = $this->envEditor->setKey('STORAGE_DICTIONARY', $storagePath);

        $this->envEditor = $this->envEditor->save();
        return true;
    }

    





    public
    static function pathFilter($path = '', $mainStoragePath = '')
    {
        if (strpos($path, '.gitignore') !== false) {
            return false;
        }
        $pathSeparator = strpos($path, '\\') === false ? '/' : '\\';
        $relativePath = str_replace($mainStoragePath, '', $path);
        $count = substr_count($relativePath, $pathSeparator);
        return !($count > 2);
    }
}
