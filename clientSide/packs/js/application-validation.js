import getCurrentLine from 'get-current-line';
import sendClientErrorToServer from '../../packs/js/client-error-receiver.js';
import { isEmpty } from 'lodash';

$(function () {
    if (window.url_to_validate) {
        $.get(window.url_to_validate)
            .done(function (data) {
                $("#app_validation_info").empty().hide();

                const keys = Object.keys(data);
                if (!isEmpty(keys)) {
                    let result = [];

                    for (var dataKey = 0; dataKey < keys.length; dataKey++) {
                        var t = data[keys[dataKey]];
                        result.push($(`
                            <li class="ml-4">
                                <a href="${t.url}">
                                    ${t.title}
                                </a>

                                ${t.message}
                            </li>
                        `));
                    }

                    $("#app_validation_info").show().append($(`
                        <ul class="pl-0 mb-0">
                            ${window.app_validate_error_message}
                        </ul>
                    `).append(result));
                } else if (window.url_to_check_can_send) {
                    canSendApp(window.url_to_check_can_send);
                }
            })
            .fail(function (e) {
                let eventLocation = getCurrentLine();
                sendClientErrorToServer('error', e?.responseText, eventLocation);
            });
    }
});

/**
 * @param {string} url
 *
 * @returns {void}
 */
function canSendApp(url) {
    $.get(url)
        .done(function (result) {
            $("#can_send_app_info").empty().hide();

            if (result == "true") {
                $("#can_send_app_info").show().append(window.can_send_app_message);
            }
        })
        .fail(function (e) {
            let eventLocation = getCurrentLine();
            sendClientErrorToServer('error', e?.responseText, eventLocation);
        });
}