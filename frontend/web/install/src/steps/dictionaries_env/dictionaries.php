<div class="loading-indicator-container" id="dictionary-loading-indicator">
    <img src="/frontend/web/install/img/ajax-loader.gif" class="loading-indicator" alt="Загрузка..." />
</div>

<h3>Результаты настройки:</h3>

<h4>Системные требования:</h4>
<div class="system-requirements-total">
    <?php if ($requirementsChecker->result['summary']['errors'] > 0): ?>
        <div class="alert alert-danger">
            Внимание! Конфигурация вашего сервера не удовлетворяет требованиям. Количество ошибок: <?php echo $requirementsChecker->result['summary']['errors']; ?>.
        </div>
    <?php elseif ($requirementsChecker->result['summary']['warnings'] > 0): ?>
        <div class="alert alert-warning">
            Конфигурация вашего сервера удовлетворяет минимальным требованиям. Количество предупреждений: <?php echo $requirementsChecker->result['summary']['warnings']; ?>.
        </div>
    <?php else: ?>
        <div class="alert alert-success">
            Конфигурация сервера удовлетворяет всем требованиям.
        </div>
    <?php endif; ?>
</div>
<h4>Конфигурация веб-сервера:</h4>
<div class="webserver-requirements-total">
    <div class="alert alert-danger">
        Произошла ошибка копирования конфигурационного файла веб-сервера.
    </div>

    <div class="alert alert-warning">
        Автоматическая настройка выбранного вами веб-сервера не поддерживается. Убедитесь, что веб-сервер настроен корректно.
    </div>

    <div class="alert alert-success">
        Файл настроек веб-сервера был успешно скопирован в корневую директорию.
    </div>
</div>
<h4>Права на директории:</h4>
<div class="file-permissions-total">
    <?php if($haveRightErrors): ?>
        <div class="alert alert-danger">
            Не на все директории установлены корректные права.
        </div>
    <?php else: ?>
        <div class="alert alert-success">
            Права на все директории установлены корректно.
        </div>
    <?php endif; ?>
</div>
<h4>Настройка базы данных</h4>
<div class="database-requirements-total">
    <div class="alert alert-danger">
        Соединение с базой данных не было настроено.
    </div>
    <div class="alert alert-success">
        Соединение с базой данных настроено. База данных развернута успешно.
    </div>
</div>
<h4>Настройка окружения и соединения с 1С</h4>
<div class="environment-requirements-total">
    <div class="alert alert-danger">
        Окружение и соединение с 1С не были настроены.
    </div>
    <div class="alert alert-success">
        Настройки окружения сохранены. Все веб-сервисы 1С доступны.
    </div>
</div>
<h4>Установка справочников</h4>
<div class="dictionary-callback-status alert alert-info">
    Справочники не были установлены.
</div>

<div class="checkbox" id="dictionary-checkbox">
    <label>
        <input type="checkbox" value="" id="omit_dictionary">
        Продолжить установку несмотря на ошибки при загрузке справочников.
    </label>
</div>
<p class="fix-errors" id="dictionary-checkbox-note">Неполная установка справочников может привести к некорректной работе портала.</p>

<p class="fix-errors">Перед установкой справочников исправьте ошибки (при их наличии).</p>
<div id="update-wrapper" style="display: none;">
    <div>
        <div class="progress-wrapper">
            <p id="progress-label">Начало обновления...</p>
            <div class="progress">
                <div id="progress-bar" class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 0;">
                    <span class="sr-only">60% Complete</span>
                </div>
            </div>
        </div>
        <div id="alert-warning-wrapper" style="display: none; margin-top: 20px">
        </div>
        <div id="alert-wrapper" style="display: none; margin-top: 20px">
            <p id="dictionary-update-error-method">Ошибка:</p>
            <div id="dictionary-update-error" class="alert alert-danger" ></div>
        </div>
        <div id="alert-success-wrapper" style="display: none; margin-top: 20px">
            <div class="alert alert-success" >
                <p id="dictionary-update-success"></p>
            </div>
        </div>
    </div>
</div>
<form name="dictionary-form" id="dictionary-form" method="post" action="">
    <button class="btn btn-primary" id="dictionary-button">Загрузить справочники</button>
    <a href="/frontend/web/install.php?r=dictionary/finish" id="finish-setup" disabled  class="btn btn-success disabled">Завершить установку</a>
</form>