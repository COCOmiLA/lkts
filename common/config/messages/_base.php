<?php
return  [
    'sourcePath'=>Yii::getAlias('@base'),
    
    
    'languages' => ['uk','ru','es'],
    
    
    
    
    'translator' => 'Yii::t',
    
    
    
    'sort' => false,
    
    
    'removeUnused' => true,

    
    
    
    
    
    
    
    
    'only' => [
        '*.php',
    ],
    
    
    
    
    'except' => [
        '.svn',
        '.git',
        '.gitignore',
        '.gitkeep',
        '.hgignore',
        '.hgkeep',
        '/messages',
        '/vendor',
        '/storage',
        '/tests'
    ],
];
