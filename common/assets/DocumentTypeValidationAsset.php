<?php

namespace common\assets;

use common\components\AssetManager\AssetBundleManager;
use yii\web\JqueryAsset;

class DocumentTypeValidationAsset extends AssetBundleManager
{
    public $js = ['props-validation/document_type_validation.js'];

    public $depends = [JqueryAsset::class];
}