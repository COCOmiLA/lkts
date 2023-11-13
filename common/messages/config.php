<?php











return [
    'interactive' => true,
    'help' => false,
    'silentExitOnException' => null,
    'sourcePath' => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..',
    'messagePath' => __DIR__,
    'languages' => [
        Yii::t('header/language-btn', 'Мнемоническое значение для языка: `Русский`') => 'ru',
        Yii::t('header/language-btn', 'Мнемоническое значение для языка: `Английский`') => 'en',
        Yii::t('header/language-btn', 'Мнемоническое значение для языка: `Украинский`') => 'uk',
        Yii::t('header/language-btn', 'Мнемоническое значение для языка: `Испанский`') => 'es',
    ],
    'translator' => 'Yii::t',
    'sort' => true,
    'overwrite' => true,
    'removeUnused' => true,
    'markUnused' => true,
    'except' => [
        '.svn',
        '.git',
        '.gitignore',
        '.gitkeep',
        '.hgignore',
        '.hgkeep',
        '/common/modules/student/components/forumIn/*',
        '/common/BaseYii.php',
        '/vendor',
        '/storage',
        '/.vscode',
    ],
    'only' => [
        '*.php',
    ],
    'ignoreCategories' => ['podium/flash', 'podium/view', 'yii'],
    'format' => 'php',
    'db' => 'db',
];
