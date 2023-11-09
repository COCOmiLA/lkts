<div class="loading-indicator-container" id="environment-loading-indicator">
    <img src="/frontend/web/install/img/ajax-loader.gif" class="loading-indicator" alt="Загрузка..." />
</div>

<div class="environment-callback-status">
    <div class="alert alert-danger">
        <strong id="error-container"></strong>
    </div>
    <div class="alert alert-success">
        <strong>Настройки сохранены. Все веб-сервисы 1С доступны.</strong>
    </div>
</div>

<form name="environment-form" id="environment-form" method="post" action="">
    <h3>Настройки приложения</h3>
    <div class="well">
        <div class="form-group">
            <label for="WebAddress">Адрес портала (домен): <span style="color:var(--red);">*</span></label>
            <input type="text" class="form-control" name="WebAddress" id="WebAddress" placeholder="">
            <span id="helpBlock" class="form-text text-muted">Указывайте адрес в виде http://{домен}/</span>
        </div>
        <div class="form-group">
            <label for="WebName">Наименование портала: <span style="color:var(--red);">*</span></label>
            <input type="text" class="form-control" name="WebName" id="WebName" placeholder="">
        </div>
    </div>

    <h3>Настройки почтового сервера</h3>
    <span id="helpBlock" class="form-text text-muted">Если не указать эти данные, то невозможно будет отправлять электронную почту</span>
    <div class="well">
        <div class="form-group">
            <label for="MailHost">Адрес почтового сервера:</label>
            <input type="text" class="form-control" name="MailHost" id="MailHost" placeholder="">
        </div>
        <div class="form-group">
            <label for="MailPort">Порт почтового сервера:</label>
            <input type="text" class="form-control" name="MailPort" id="MailPort" placeholder="">
        </div>
        <div class="form-group">
            <label for="MailProtocol">Протокол подключения к почтовому серверу:</label>
            <select class="form-control" name="MailProtocol" id="MailProtocol">
                <option value="">Выберите протокол подключения к почтовому серверу</option>
                <option value="tls">TLS</option>
                <option value="ssl">SSL</option>
                <option value="unsafe">Небезопасное подключение</option>
            </select>
        </div>
        <div class="form-group">
            <label for="MailUsername">Логин для авторизации на почтовом сервере:</label>
            <input type="text" class="form-control" name="MailUsername" id="MailUsername" placeholder="">
        </div>
        <div class="form-group">
            <label for="MailPassword">Пароль для авторизации на почтовом сервере:</label>
            <input type="password" class="form-control" name="MailPassword" id="MailPassword" placeholder="">
        </div>
        <div class="form-group">
            <label for="WebAdminEmail">Электронная почта администратора:</label>
            <input type="text" class="form-control" name="WebAdminEmail" id="WebAdminEmail" placeholder="">
        </div>
        <div class="form-group">
            <label for="WebOutEmail">Исходящий адрес электронной почты:</label>
            <input type="text" class="form-control" name="WebOutEmail" id="WebOutEmail" placeholder="">
        </div>
    </div>

    <h3>Настройки для подключения к сервисам 1С</h3>
    <div class="well">
        <div class="form-group">
            <label for="OdinWeb">Адрес публикации базы 1С: <span style="color:var(--red);">*</span></label>
            <input type="text" class="form-control" name="OdinWeb" id="OdinWeb" placeholder="http://">
            <span id="helpBlock" class="form-text text-muted">Обратите внимание на регистр наименования базы данных. Пример адреса: http://example.com/DbName</span>
        </div>
        <h4>Веб-сервис "ЛК Студента"</h4>
        <div class="well">
            <div class="form-group">
                <label for="OdinNameStud">Имя пользователя 1С: <span style="color:var(--red);">*</span></label>
                <input type="text" class="form-control" name="OdinNameStud" id="OdinNameStud" placeholder="">
            </div>
            <div class="form-group">
                <label for="OdinPasswordStud">Пароль пользователя 1С:</label>
                <input type="password" class="form-control" name="OdinPasswordStud" id="OdinPasswordStud" placeholder="">
            </div>
            <div class="form-group">
                <label for="OdinWebStud">Имя публикации на веб-сервере: <span style="color:var(--red);">*</span></label>
                <input type="text" class="form-control" name="OdinWebStud" id="OdinWebStud" value="/ws/Study.1cws?wsdl">
                <span id="helpBlock" class="form-text text-muted">Обратите внимание на регистр наименования базы данных. Пример адреса: /ws/Study.1cws?wsdl</span>
            </div>
        </div>
        <h4>Веб-сервис "ЛК Поступающего"</h4>
        <div class="well">
            <div class="form-group">
                <label for="OdinNameAbit">Имя пользователя 1С: <span style="color:var(--red);">*</span></label>
                <input type="text" class="form-control" name="OdinNameAbit" id="OdinNameAbit" placeholder="">
            </div>
            <div class="form-group">
                <label for="OdinPasswordAbit">Пароль пользователя 1С:</label>
                <input type="password" class="form-control" name="OdinPasswordAbit" id="OdinPasswordAbit" placeholder="">
            </div>
            <div class="form-group">
                <label for="OdinWebAbit">Имя публикации на веб-сервере: <span style="color:var(--red);">*</span></label>
                <input type="text" class="form-control" name="OdinWebAbit" id="OdinWebAbit" value="/ws/webabit.1cws?wsdl">
                <span id="helpBlock" class="form-text text-muted">Обратите внимание на регистр наименования базы данных. Пример адреса: /ws/webabit.1cws?wsdl</span>
            </div>
        </div>
        <h4>Веб-сервис "ЛК WebApplication"</h4>
        <div class="well">
            <div class="form-group">
                <label for="OdinNameWebApp">Имя пользователя 1С: <span style="color:var(--red);">*</span></label>
                <input type="text" class="form-control" name="OdinNameWebApp" id="OdinNameWebApp" placeholder="">
            </div>
            <div class="form-group">
                <label for="OdinPasswordWebApp">Пароль пользователя 1С:</label>
                <input type="password" class="form-control" name="OdinPasswordWebApp" id="OdinPasswordWebApp" placeholder="">
            </div>
            <div class="form-group">
                <label for="OdinWebWebApp">Имя публикации на веб-сервере: <span style="color:var(--red);">*</span></label>
                <input type="text" class="form-control" name="OdinWebWebApp" id="OdinWebWebApp" value="/ws/webapplication.1cws?wsdl">
                <span id="helpBlock" class="form-text text-muted">Обратите внимание на регистр наименования базы данных. Пример адреса: /ws/webapplication.1cws?wsdl</span>
            </div>
        </div>
    </div>

    <input type="submit" value="Сохранить и проверить" class="btn btn-primary">
</form>