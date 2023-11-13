<?php






Yii::setAlias('@webroot', Yii::getAlias('@frontend/web'));
Yii::setAlias('@web', '/');

return [
    
    'jsCompressor' => 'uglifyjs {from} -o {to}',
    
    'cssCompressor' => 'yuicompressor --type css {from} -o {to}',

    
    'bundles' => [
        'frontend\assets\FrontendAsset',
        'yii\web\YiiAsset',
        'yii\web\JqueryAsset',
    ],

    
    'targets' => [
        'all' => [
            'class' => 'yii\web\AssetBundle',
            'basePath' => '@webroot',
            'baseUrl' => '@web',
            'js' => 'bundle/{hash}.js',
            'css' => 'bundle/{hash}.css',
        ],
    ],

    
    'assetManager' => [
        'basePath' => '@webroot/assets',
        'baseUrl' => '@web/assets'
    ],
];