<?php






use common\components\AppUpdate;
use kartik\grid\GridView;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View as ViewAlias;

$this->title = 'Управление справочниками';
$this->params['breadcrumbs'][] = $this->title;


$alert = Yii::$app->session->getFlash('successFias');
if ($alert) {
    echo Html::tag('div', $alert, ['class' => 'alert alert-success', 'role' => 'alert']);
} else {
    $alert = Yii::$app->session->getFlash('errorFias');
    if ($alert) {
        echo Html::tag('div', $alert, ['class' => 'alert alert-danger', 'role' => 'alert']);
    }
}

?>

<div id="update-wrapper" style="display: none;">
    <div class="progress-wrapper">
        <p id="progress-label">
            Начало обновления...
        </p>

        <div class="progress">
            <div id="progress-bar" class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 0;">
                <span class="sr-only">
                    60% Complete
                </span>
            </div>
        </div>
    </div>

    <div id="alert-warning-wrapper" style="display: none; margin-top: 20px">
    </div>

    <div id="alert-wrapper" style="display: none; margin-top: 20px">
        <p id="dictionary-update-error-method">
            Ошибка:
        </p>

        <div id="dictionary-update-error" class="alert alert-danger"></div>
    </div>

    <div id="alert-success-wrapper" style="display: none; margin-top: 20px">
        <div class="alert alert-success" id="dictionary-update-success"></div>
    </div>
</div>

<table class="table">
    <tbody>
        <tr>
            <td colspan="2">
                <h2>Основные справочники</h2>
                <button style="margin-top: 20px;margin-bottom: 20px;" class="update-dictionaries btn btn-primary">
                    Обновить
                </button>
                <a href="<?php echo Url::to(['/dictionary/restore-dictionary']) ?>" style="margin-top: 20px;margin-bottom: 20px;margin-left: 10px;" class="btn btn-info">
                    Заменить ссылки на устаревшие справочные данные
                </a>
                <?= GridView::widget([
                    'hover' => true,
                    'headerContainer' => ['class' => 'thead-light'],
                    'tableOptions' => ['class' => 'table-sm'],
                    'striped' => false,
                    'summary' => false,
                    'pager' => [
                        'firstPageLabel' => '<<',
                        'prevPageLabel' => '<',
                        'nextPageLabel' => '>',
                        'lastPageLabel' => '>>',
                    ],
                    'dataProvider' => new ArrayDataProvider([
                        'allModels' => AppUpdate::GetArrayOfDictionaries(),
                        'key' => 'key',
                        'pagination' => false
                    ]),
                    'id' => 'custom-dictionary-grid',
                    'columns' => [
                        [
                            'class' => \yii\grid\CheckboxColumn::class,
                            'name' => 'key'
                        ],
                        [
                            'attribute' => 'name',
                            'label' => 'Наименование справочника',
                        ]
                    ],
                ]); ?>
            </td>
        </tr>
        <?php if ($iCanLoad_KLADR_from_file) : ?>
            <tr>
                <?php
                $url = Url::toRoute(['/settings/kladr']);
                ?>
                <td style='ertical-align: middle;'>
                    Обновление адресного классификатора из базы КЛАДР <em>(обновление может занять продолжительное
                        время)</em>
                </td>

                <td>
                    <?= Html::a(
                        "Обновить",
                        $url,
                        array_merge(
                            [
                                'class' => 'btn btn-primary',
                                'data-confirm' => 'Вы уверены, что хотите обновить данные справочника КЛАДР?',
                                'data-method' => 'get',
                            ]
                        )
                    ); ?>
                </td>
            </tr>
        <?php endif; ?>
        <?php if ($iCanLoad_KLADR_from_1C) : ?>
            <tr>
                <?php
                $url = Url::toRoute(['/fias/index']);
                ?>
                <td style='vertical-align: middle;'>
                    Обновление адресного классификатора из базы ФИАС в Информационной системе вуза (экспериментально)
                </td>

                <td>
                    <?= Html::a(
                        "Обновить",
                        $url,
                        [
                            'class' => 'btn btn-primary',
                        ],
                    ); ?>
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if (!$iCanLoad_KLADR_from_1C && !$iCanLoad_KLADR_from_file) {
    foreach ($filds as $key => $fild) {
        if (isset($fild)) {
            echo Html::tag(
                'div',
                $fild,
                [
                    'role' => 'alert',
                    'class' => 'alert alert-danger',
                ]
            );
        }
    }
}

$js = <<<JS
// обновление справочников
var doneMethods = 0;
var progressStep = 0;
var dictionariesArray = [];
var dictionariesArrayKeys = [];
var warnings = [];

function updateOneDictionary(index) {
    if (dictionariesArrayKeys.length === index) {
        makeRequest("linkRefODataFields", "Связь с элементами справочников");
        finishProgressBar();
        setCurrentDictionary("Готово!");
        showTextSuccess("Все справочники успешно установлены.");
        return true;
    }

    var method = dictionariesArrayKeys[index];
    setCurrentDictionary(dictionariesArray[method] || method);
    return fetchMethod(method)
        .done(function (res) {
            if (res && res.status) {
                increaseDoneMethods();
                updateOneDictionary(index + 1);
            } else {
                showTextError(res.error_message);
            }
        })
        .catch(function (e) {
            if (processErrorResponse(method, e)) {
                increaseDoneMethods();
                updateOneDictionary(index + 1);
            }
        });
}

function updateDictionary() {
    updateOneDictionary(0);
}

function showUpdateRow() {
    $("#update-wrapper").show();
}

function increaseDoneMethods() {
    doneMethods += 1;
    var progress = doneMethods * progressStep;
    $("#progress-bar").width(progress + "%");
}

function resetProgress() {
    doneMethods = 0;
    progressStep = 0;
    $("#progress-bar").width(0); // Clear Warnings

    warnings = [];
    redrawWarning(); // Clear Errors

    $("#alert-wrapper").hide();
    $("#alert-success-wrapper").hide();
}

function setCurrentDictionary(dictionaryName) {
    $("#progress-label").html(dictionaryName);
}

function finishProgressBar() {
    $("#progress-bar").width(100 + "%");
}

function showTextError(err) {
    $("#alert-wrapper").show();
    $("#dictionary-update-error").html(err);
    var prev = localStorage.getItem("dictionary-callback");
    localStorage.setItem("dictionary-callback", prev + err);
}

function showTextSuccess(text) {
    $("#alert-success-wrapper").show();
    $("#dictionary-update-success").html(text);
    localStorage.removeItem("dictionary-callback");
}

function appendWarning(dictionary, warning) {
    warnings.push({
        dictionary: dictionary,
        warning: warning,
    });
    redrawWarning();
}

function redrawWarning() {
    // vue.js is better than this
    var wrapper = $("#alert-warning-wrapper");
    wrapper.show();
    wrapper.html("");
    for (var warning_idx = 0; warning_idx < warnings.length; warning_idx++) {
        var warning = warnings[warning_idx];
        var alert = document.createElement("div");
        alert.classList.add("alert");
        alert.classList.add("alert-warning");
        var span = document.createElement("span");
        span.innerHTML = "<strong>" + warning.dictionary + "</strong>: " + warning.warning;
        alert.appendChild(span);
        wrapper.append(alert);
    }
}

function fetchMethod(method) {
    return $.ajax({
        url: "/admin/dictionary/update-one-dictionary",
        data: {
            method: method,
        },
        type: "get",
    });
}

function processErrorResponse(method, e) {
    var message = "";

    if (e.responseJSON && e.responseJSON.message && e.responseJSON.message === "no-data-warning") {
        appendWarning(dictionariesArray[method], "Нет данных");
        return true;
    }
    if (e.responseText && e.responseText.includes("no-data-warning")) {
        appendWarning(dictionariesArray[method], "Нет данных");
        return true;
    }

    console.error(e);

    if (e.responseJSON && e.responseJSON.message) {
        message = e.responseJSON.message;

        if (e.responseJSON["stack-trace"]) {
            try {
                message = message + "<br><br>" + e.responseJSON["stack-trace"].join("<br>");
            } catch (er) {
                console.error(er);
            }
        }
    } else if (e.responseText) {
        message = e.responseText;
    } else {
        message = "Внутренняя ошибка сервера";
    }

    showTextError(message);
    return false;
}

function makeRequest(method, methodName) {
    setCurrentDictionary(methodName || dictionariesArray[method]);

    try {
        fetchMethod(method).done(function (res) {
            if (res && res.error_message) {
                showTextError(res.error_message);
            }
            return res && res.status;
        });
    } catch (e) {
        return processErrorResponse(method, e);
    }
}

$(function () {
    $('[data-toggle="tooltip"]').tooltip();
});
$("#custom-dictionary-grid input:checkbox").not(".disabled_checkbox").prop("checked", true);

function checkSelection() {
    var sr = $("#custom-dictionary-grid").yiiGridView("getSelectedRows");

    if (sr.length === 0) {
        alert("Операция не может быть выполнена, необходимо выбрать хотя бы один справочник");
    }

    return sr;
}

$(".update-dictionaries").click(function (e) {
    dictionariesArrayKeys = checkSelection();

    if (dictionariesArrayKeys.length > 0) {
        resetProgress();
        showUpdateRow();
        $.ajax({
            url: "/admin/dictionary/get-dictionary-to-update",
            type: "get",
        })
            .done(function (res) {
                progressStep = Math.round(70 / dictionariesArrayKeys.length);
                dictionariesArray = res;
                updateDictionary();
            })
            .catch(function (res) {
                console.error(res);
            });
    }
});

JS;

$this->registerJs($js, ViewAlias::POS_END);

?>

<style>
    #progress-label {
        font-weight: bold;
    }
</style>