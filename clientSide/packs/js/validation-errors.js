import getCurrentLine from 'get-current-line';
import sendClientErrorToServer from '../../packs/js/client-error-receiver.js';

function checkAllApplication() {
    if (window.url_to_validate) {
        var alert = $(".validation-errors-content .alert");
        $.get(window.url_to_validate)
            .done(function (data) {
                alert.empty().hide();

                if (data.length > 0) {
                    var result = [];

                    for (var dataKey = 0; dataKey < data.length; dataKey++) {
                        var t = data[dataKey];
                        result.push(
                            $(
                                '<li><a href="'
                                    .concat(t.url, '">')
                                    .concat(t.title, "</a>")
                                    .concat(t.message, "</li>")
                            )
                        );
                    }

                    alert
                        .show()
                        .append(window.app_validate_error_message)
                        .append($('<ul class="validation-error-ul"></ul>').append(result));
                } else if (window.url_to_check_can_send) {
                    canSendApp(window.url_to_check_can_send);
                }
            })
            .fail(function (e) {
                let eventLocation = getCurrentLine();
                sendClientErrorToServer('error', e?.responseText, eventLocation);
            });
    }
}
