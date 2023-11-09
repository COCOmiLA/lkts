<div class="loading-indicator-container" id="migrations-loading-indicator">
    <img src="/frontend/web/install/img/ajax-loader.gif" class="loading-indicator" alt="Загрузка..." />
</div>
<div class="migrations-callback-status">
    <div class="alert alert-danger">
        Ошибка применения миграций
    </div>
    <div class="alert alert-success">
        Все миграции применены
    </div>
</div>
<form name="migrations-form" id="migrations-form" method="post" action="">
    <input id="migrations-form-submit" type="submit" value="Применить миграции" class="btn btn-primary">
</form>