<div class="alert alert-danger" id="commonServerErrors" style="display:none;">
    <ul id="commonServerErrorsList"></ul>
</div>
<div class="webserver-callback-status">
    <div class="alert alert-danger" id="noServerErrors">
        <strong>Ошибка копирования конфигурационного файла веб-сервера.</strong>
    </div>
    <div class="alert alert-danger" id="Apache_ServerErrors">
        <strong>Не настроен модуль преобразований адресов для сайта.</strong>
        Необходимо активировать расширение mod_rewrite и разрешить использование .htaccess. Проверьте, что в настройках сервера есть такие строки:<br>
        <var><samp style="padding-left: 25px;">&lt;Directory /var/www/html&gt;</samp></var><br>
        <var><samp style="padding-left: 50px;">Options Indexes FollowSymLinks MultiViews</samp></var><br>
        <var><samp style="padding-left: 50px;">AllowOverride All</samp></var><br>
        <var><samp style="padding-left: 50px;">Require all granted</samp></var><br>
        <var><samp style="padding-left: 25px;">&lt;/Directory&gt;</samp></var><br>
        , где /var/www/html - путь к папке, в которую установлен портал.
    </div>
    <div class="alert alert-danger" id="IIS_ServerErrors">
        <strong>Не настроен модуль преобразований адресов для сайта.</strong>

        Необходимо установить расширение <strong><a href="https://www.iis.net/downloads/microsoft/url-rewrite">urlrewrite</a></strong>
    </div>
    <div id="webserver-alert-success" class="alert alert-success">
        <strong>Конфигурационный файл веб-сервера скопирован успешно.</strong>
    </div>
</div>
<h3>Выберите используемый веб-сервер:</h3>
<form name="webserver-form" id="webserver-form" method="post" action="">
    <div class="form-check"><label><input type="radio" name="group1" value="apache">Apache</label></div>
    <div class="form-check"><label><input type="radio" name="group1" value="iis">IIS</label></div>
    <div class="form-check"><label><input type="radio" name="group1" value="other">Другой (nginx и др.)</label></div>
    <div class="web-server-notes">
        <div id="apache" class="desc">После нажатия на кнопку "Сохранить" в корневую директорию будет скопирован файл настроек .htaccess</div>
        <div id="iis" class="desc">После нажатия на кнопку "Сохранить" в корневую директорию будет скопирован файл настроек web.config</div>
        <div id="other" class="desc">Для настройки веб-сервера обратитесь к системному администратору (для nginx есть образец конфигурации в директории confs/vuz.conf)</div>
    </div>
    <input type="submit" value="Сохранить" class="btn btn-primary">
</form>
<h3>Права на папки:</h3>

<?php
require_once('folders_requirements.php');
$folders = getFolders();
?>
<table class="table table-bordered">
    <tr>
        <th>Директория</th>
        <th>Права</th>
    </tr>
    <?php $haveRightErrors = false;
    foreach ($folders as $dir => $perms) :
        if ($perms) {
            $cl = 'success';
        } else {
            $cl = 'danger';
        } ?>
        <tr class="<?php echo $cl; ?>">
            <td style="width:30%;"><?php echo $dir; ?></td>
            <td style="width:70%;">
                <?php if ($perms) {
                    $message = 'Права на директорию установлены корректно';
                } else {
                    $message = 'Необходимо установить корректные права на директорию (владелец/группа и права на запись)';
                    $haveRightErrors = true;
                }
                echo $message; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>