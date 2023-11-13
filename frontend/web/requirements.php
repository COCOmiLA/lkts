<?php











$frameworkPath = dirname(__FILE__) . '/../../vendor/yiisoft/yii2';
if (!is_dir($frameworkPath)) {
    echo '<h1>Error</h1>';
    echo '<p><strong>The path to yii framework seems to be incorrect.</strong></p>';
    echo '<p>You need to install Yii framework via composer or adjust the framework path in file <abbr title="' . __FILE__ . '">' . basename(__FILE__) . '</abbr>.</p>';
    echo '<p>Please refer to the <abbr title="' . dirname(__FILE__) . '/README.md">README</abbr> on how to install Yii.</p>';
}
require_once($frameworkPath . '/requirements/YiiRequirementChecker.php');
$requirementsChecker = new YiiRequirementChecker();



$requirements = array(
    
    array(
        'name' => 'PDO extension',
        'mandatory' => true,
        'condition' => extension_loaded('pdo'),
        'by' => 'All DB-related classes',
    ),
    array(
        'name' => 'PDO MySQL extension',
        'mandatory' => true,
        'condition' => extension_loaded('pdo_mysql'),
        'by' => 'All DB-related classes',
        'memo' => 'Required for MySQL database.',
    ),







    array(
        'name' => 'Intl extension',
        'mandatory' => true,
        'condition' => extension_loaded('intl'),
        'by' => 'Internationalization Functions',
        'memo' => 'Internationalization Functions'
    ),
    array(
        'name' => 'Fileinfo extension',
        'mandatory' => true,
        'condition' => extension_loaded('fileinfo'),
        'by' => 'Fileinfo Functions',
        'memo' => 'Fileinfo Functions'
    ),
    array(
        'name' => 'Soap client extension',
        'mandatory' => true,
        'condition' => extension_loaded('soap'),
        'by' => 'Fileinfo Functions',
        'memo' => 'Fileinfo Functions'
    ),
    array(
        'name' => 'Mbstring extension',
        'mandatory' => true,
        'condition' => extension_loaded('mbstring'),
        'by' => 'Multibyte strings',
        'memo' => 'Multibyte strings'
    ),
    array(
        'name' => 'GD extension',
        'mandatory' => true,
        'condition' => extension_loaded('gd'),
        'by' => 'Work with images',
        'memo' => 'Work with images'
    ),
    'phpExposePhp' => array(
        'name' => 'Expose PHP',
        'mandatory' => false,
        'condition' => $requirementsChecker->checkPhpIniOff("expose_php"),
        'by' => 'Security reasons',
        'memo' => '"expose_php" should be disabled at php.ini',
    ),
    'phpAllowUrlInclude' => array(
        'name' => 'PHP allow url include',
        'mandatory' => false,
        'condition' => $requirementsChecker->checkPhpIniOff("allow_url_include"),
        'by' => 'Security reasons',
        'memo' => '"allow_url_include" should be disabled at php.ini',
    ),
    'phpSmtp' => array(
        'name' => 'PHP mail SMTP',
        'mandatory' => false,
        'condition' => strlen(ini_get('SMTP')) > 0,
        'by' => 'Email sending',
        'memo' => 'PHP mail SMTP server required',
    ),
);
$requirementsChecker->checkYii()->check($requirements)->render();