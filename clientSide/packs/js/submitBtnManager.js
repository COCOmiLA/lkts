/**
 * Скрипт занимающийся обработкой нажатий на `submit-кнопки`.
 *
 * * Функции:
 *   > Дисаблит кнопку после того она была нажата. Позволяет избежать повторной отправки сабмит-запроса на сервер;
 *   > Генерирует тултип на задисабленой кнопке с текстом, что идёт обработка запроса;
 */

import getCurrentLine from 'get-current-line';
import sendClientErrorToServer from '../../packs/js/client-error-receiver.js';

$(document).on("click", ".anti-clicker-btn", function () {
    let btn = $(this);

    btn.parents("form").on("beforeSubmit", function () {
        submitBtnDisabler(btn);
        const message = btn.data("tooltip_title");

        if (message && message.length > 0) {
            const placement = btn.data("placement");

            if (placement && placement.length > 0) {
                submitBtnTooltipper(btn, message, placement);
            } else {
                submitBtnTooltipper(btn, message);
            }
        }
    });
});

/**
 * @param {object} submitBtn
 * @param {boolean} disabledStatus
 *
 * @return {void}
 */
function submitBtnDisabler(submitBtn) {
    const disabledStatus = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;

    submitBtn.prop("disabled", disabledStatus);
}

/**
 * @param {object} submitBtn
 * @param {string} tooltipMessage
 * @param {string} tooltipPlacement
 *
 * @return {void}
 */
function submitBtnTooltipper(submitBtn, tooltipMessage) {
    try {
        let tooltipPlacement =
            arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : "top";

        submitBtn.attr("title", tooltipMessage);
        submitBtn.attr("data-original-title", tooltipMessage);

        if (tooltipPlacement.length < 0) {
            tooltipPlacement = "top";
        }

        submitBtn.attr("data-placement", tooltipPlacement);
        submitBtn.tooltip("show");
    } catch (e) {
        const eventLocation = getCurrentLine();
        sendClientErrorToServer('error', e?.stack, eventLocation);
    }
}
