<?php

$frameworkPath = dirname(__FILE__) . '/requirements';
if (!is_dir($frameworkPath)) {
    echo '<h1>Ошибка</h1>';
    echo '<p><strong>Невозможно найти Yii фреймворк.</strong></p>';
}

$phppath = 'php';
if (isset($_COOKIE['phppath'])) $phppath = $_COOKIE['phppath'];




require_once('customs_requirements.php');

$requirementsChecker = getRequirementChecker($frameworkPath, $phppath);

$requirementsChecker->render();
if (!$requirementsChecker->checkPhpPath($phppath)) {
    echo ('
    <form name="phpexe-form" id="phpexe-form" method="post" action="">
        <div class="form-group">
            <label for="phppath">Путь к исполняемому файлу php</label>
            <input type="text" class="form-control" name="phppath" id="phppath" placeholder="">
            <span id="helpBlock" class="form-text text-muted">Пример пути: C:\php\php.exe</span>
        </div>
        
        <input type="submit" value="Сохранить и проверить" class="btn btn-primary">
    </form>
    ');
}
