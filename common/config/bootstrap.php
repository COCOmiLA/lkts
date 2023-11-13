<?php

$storageDictionary = getenv('STORAGE_DICTIONARY');
if (empty($storageDictionary)) {
    $storageDictionary = __DIR__ . '/../../storage';
}


Yii::setAlias('@base', realpath(__DIR__ . '/../../'));
Yii::setAlias('@storage', realpath($storageDictionary));
Yii::setAlias('@tests', realpath(__DIR__ . '/../../tests'));
Yii::setAlias('@common', realpath(__DIR__ . '/../../common'));
Yii::setAlias('@backend', realpath(__DIR__ . '/../../backend'));
Yii::setAlias('@console', realpath(__DIR__ . '/../../console'));
Yii::setAlias('@tests', realpath(__DIR__ . '/../../tests'));
Yii::setAlias('@api', realpath(__DIR__ . '/../../api'));
Yii::setAlias('@frontend', realpath(__DIR__ . '/../../frontend'));
Yii::setAlias('@abiturient', realpath(__DIR__ . '/../../common/modules/abiturient'));
Yii::setAlias('@backendAssets', realpath(__DIR__ . '/../../backend/web/assets'));
Yii::setAlias('@frontendAssets', realpath(__DIR__ . '/../../frontend/web/assets'));
Yii::setAlias('@abiturientViews', realpath(__DIR__ . '/../../common/modules/abiturient/views/abiturient'));

Yii::setAlias('@backendUrl', getenv('BACKEND_URL'));
Yii::setAlias('@storageUrl', getenv('STORAGE_URL'));
Yii::setAlias('@frontendUrl', getenv('FRONTEND_URL'));


require(__DIR__ . '/../shortcuts.php');
