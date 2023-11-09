/**
 * Скрипт занимающийся обработкой нажатий на `aJax-кнопки`.
 *
 * * Функции:
 *   > Дисаблит кнопку после того она была нажата. Позволяет избежать повторной отправки запроса на сервер;
 *   > Генерирует тултип на задисабленой кнопке с текстом, что идёт обработка запроса;
 *   > Расдисабливает кнопку по завершению `aJax`-запроса (в не зависимости "success" или "error")
 */

import getCurrentLine from 'get-current-line';
import { isEmptyValue } from '../../components/chat/common.js';
import sendClientErrorToServer from '../../packs/js/client-error-receiver.js';

/**
 * @param {string} url
 * @param {string} type
 * @param {JSON} data
 * @param {object} btn - это `jQuery`-кнопка
 * @param {function} functionOnSuccess
 * @param {function|null} functionOnError
 * @param {string} tooltipMessage
 * @param {string} tooltipPlacement
 * @param {object} additional_params
 *
 * @return {boolean}
 */
function ajaxSenderFunc(url, type, data, btn, functionOnSuccess, functionOnError, tooltipMessage) {
    const tooltipPlacement =
        arguments.length > 7 && arguments[7] !== undefined ? arguments[7] : "top";

    let additional_params = arguments.length > 8 && arguments[8] !== undefined ? arguments[8] : {};

    const request_data = {
        url: url,
        type: type,
        data: data,
        beforeSend: function beforeSend() {
            btnDisabler(btn);
            beforeSendRoutine(btn, tooltipMessage, tooltipPlacement);
        },
        success: function success(response) {
            /** @var {string} response */

            btnDisabler(btn, false);
            successRoutine(btn);

            if (typeof functionOnSuccess == "function") {
                functionOnSuccess(response);
            }
        },
        error: function error(response) {
            /** @var {object} response */

            btnDisabler(btn, false);
            errorRoutine(btn, response?.responseText);

            const eventLocation = getCurrentLine();
            sendClientErrorToServer('error', response?.responseText, eventLocation);

            if (functionOnError) {
                functionOnError(response);
            }
        },
    };
    return $.ajax(Object.assign(request_data, additional_params));
}

window.ajaxSender = ajaxSenderFunc;

/**
 * @param {object} btn
 * @param {boolean} disabledStatus
 *
 * @return {void}
 */
function btnDisabler(btn) {
    const disabledStatus = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;

    btn.prop("disabled", disabledStatus);
}

/**
 * @param {object} btn
 * @param {string} tooltipMessage
 * @param {string} tooltipPlacement
 *
 * @return {void}
 */
function beforeSendRoutine(btn, tooltipMessage) {
    try {
        let tooltipPlacement =
            arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : "top";

        if (btn.hasClass("error_tooltip")) {
            btn.removeClass("error_tooltip");
        }

        btn.attr("title", tooltipMessage);
        if (tooltipPlacement.length < 0) {
            tooltipPlacement = "top";
        }

        btn.attr("data-placement", tooltipPlacement);
        btn.tooltip("show");
    } catch (e) {
        const eventLocation = getCurrentLine();
        sendClientErrorToServer('error', e?.stack, eventLocation);
    }
}

/**
 * @param {object} btn
 *
 * @return {void}
 */
function successRoutine(btn) {
    try {
        btn.removeAttr("title");
        btn.removeAttr("data-placement");
        btn.tooltip("dispose");
    } catch (e) {
        const eventLocation = getCurrentLine();
        sendClientErrorToServer('error', e?.stack, eventLocation);
    }
}

function strip_html(input) {
    let doc = new DOMParser().parseFromString(input, "text/html");
    return doc.documentElement.textContent;
}

/**
 * @param {object} btn
 * @param {string} tooltipMessage
 *
 * @return {void}
 */
function errorRoutine(btn, tooltipMessage) {
    try {
        if (!btn.hasClass("error_tooltip")) {
            btn.addClass("error_tooltip");
        }

        btn.attr("data-html", "true");
        btn.attr("title", strip_html(tooltipMessage));
        btn.attr("data-original-title", strip_html(tooltipMessage));
        btn.tooltip("show");

        renderErrorAlert(btn)
    } catch (e) {
        const eventLocation = getCurrentLine();
        sendClientErrorToServer('error', e?.stack, eventLocation);
    }

    /**
     * @param {object} btn
     *
     * @return {void}
     */
    function renderErrorAlert(btn) {
        if (isEmptyValue(alertErrorMessageForAjaxBtnManager)) { return; }

        const alert = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                ${alertErrorMessageForAjaxBtnManager}

                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;

        $(btn.parents('div')[0])?.prepend(alert);
    }
}
