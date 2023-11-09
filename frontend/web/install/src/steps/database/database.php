<div class="loading-indicator-container" id="db-loading-indicator">
    <img src="/frontend/web/install/img/ajax-loader.gif" class="loading-indicator" alt="Загрузка..."/>
</div>
<div class="database-callback-status">
    <div class="alert alert-danger">
        <strong id="error-container"></strong>
    </div>
    <div class="alert alert-success">
        <strong>База данных развернута успешно</strong>
    </div>
</div>
<h3>Укажите данные для подключения к серверу баз данных:</h3>
<form name="database-form" id="database-form" method="post" action="">
    <div class="form-group">
        <label for="DbType">Тип СУБД</label>
        <select name="DbType" class="form-control" id="DbType">
            <option value="mysql">MySQL</option>
            <option value="pgsql">PostgreSQL</option>
        </select>
    </div>
    <div class="form-group">
        <label for="DbServerAddress">Адрес сервера <span style="font-size: smaller;">(для указания порта СУБД используйте синтаксис "hostname:port")</span></label>
        <input type="text" class="form-control" name="ServerAddress" id="DbServerAddress" placeholder="">
    </div>
    <div class="form-group">
        <label for="DbUserName">Имя пользователя</label>
        <input type="text" class="form-control" name="DbUserName" id="DbUserName" placeholder="">
    </div>
    <div class="form-group">
        <label for="DbUserPassword">Пароль</label>
        <input type="password" class="form-control" name="DbUserPassword" id="DbUserPassword" placeholder="">
    </div>
    <div class="form-group">
        <label for="DbName">Наименование базы данных</label>
        <input type="text" class="form-control" name="DbName" id="DbName" placeholder="">
        <span id="helpBlock" class="form-text text-muted">Если указанная база данных не существует, она будет создана (поддерживается только MySQL).
            В данном случае у пользователя должно быть достаточно прав для создания базы данных.</span>
    </div>

    <input type="submit" value="Развернуть базу данных" class="btn btn-primary">
</form>
