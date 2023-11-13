<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_GET['r'])) {
    require_once('install/router.php');
} else {
?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title>Портал вуза для Университет ПРОФ</title>

        <!-- Bootstrap -->
        <link href="/frontend/web/install/css/bootstrap.min.css" rel="stylesheet">
        <link href="/frontend/web/install/css/style.css" rel="stylesheet">

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>

    <body>
        <nav class="navbar navbar-inverse fixed-top">
            <div class="container">
                <div class="navbar-header">
                    <a class="navbar-brand" href="#">Портал вуза для Университет ПРОФ</a>
                </div>
            </div>
        </nav>
        <div class="container">
            <div class="col-12 install-container">
                <div class="progress">
                <div class="main-progress-bar progress-bar bg-success bg-striped progress-bar-animated" role="progressbar"
                     aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="step-container">
                    <div class="steps">
                        <fieldset>
                            <h2>Шаг 1: Проверка системных требований</h2>

                            <?php require_once('install/src/steps/system_requirements/system_requirements.php'); ?>

                            <input id="button_requirements" type="button" name="password" class="next btn btn-success next-button disabled" value="Далее" />
                            <br>
                        </fieldset>
                    </div>
                    <div class="steps">
                        <fieldset>
                            <h2>Шаг 2: Настройка веб-сервера и прав на директории</h2>

                            <?php require_once('install/src/steps/webserver/webserver.php'); ?>

                            <input id="button_webserver" type="button" name="next" class="next btn btn-success next-button disabled" value="Далее" />
                            <input type="button" name="previous" class="previous btn btn-outline-secondary previous-button" value="Назад" />
                        </fieldset>
                    </div>
                    <div class="steps">
                        <fieldset>
                            <h2> Шаг 3: Настройка базы данных</h2>

                            <?php require_once('install/src/steps/database/database.php'); ?>

                            <input id="button_database" type="button" name="next" class="next btn btn-success next-button disabled" value="Далее" />
                            <input type="button" name="previous" class="previous btn btn-outline-secondary previous-button" value="Назад" />
                        </fieldset>
                    </div>
                    <div class="steps">
                        <fieldset>
                            <h2> Шаг 4: Применение миграций</h2>

                            <?php require_once('install/src/steps/migrations/migrations.php'); ?>

                            <input id="button_migrations" type="button" name="next" class="next btn btn-success next-button disabled" value="Далее" />
                            <input type="button" name="previous" class="previous btn btn-outline-secondary previous-button" value="Назад" />
                        </fieldset>
                    </div>
                    <div class="steps">
                        <fieldset>
                            <h2>Шаг 5: Настройка окружения и соединения с 1С</h2>

                            <?php require_once('install/src/steps/environment/environment.php'); ?>

                            <input id="button_environment" type="button" name="next" class="next btn btn-success next-button disabled" value="Далее" />
                            <input type="button" name="previous" class="previous btn btn-outline-secondary previous-button" value="Назад" />
                        </fieldset>
                    </div>
                    <div class="steps">
                        <fieldset>
                            <h2>Шаг 6: Завершение установки и загрузка справочников из 1С</h2>

                            <?php require_once('install/src/steps/dictionaries_env/dictionaries.php'); ?>

                            <input type="button" name="previous" class="previous btn btn-success previous-button" value="Назад" />
                        </fieldset>
                    </div>
                </div>
            </div>
        </div>

        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script src="/frontend/web/install/js/jquery.min.js"></script>
        <!-- Include all compiled plugins (below), or include individual files as needed -->
        <script src="/frontend/web/install/js/bootstrap.min.js"></script>
        <script src="/frontend/web/install/js/js.cookie.js"></script>
        <script src="/frontend/web/install/js/install.js"></script>
    </body>

    </html>
<?php } ?>