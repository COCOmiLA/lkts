import getCurrentLine from 'get-current-line';
import sendClientErrorToServer from '../../packs/js/client-error-receiver.js';

$(document).ready(function () {
    function getSelectedRegions() {
        var sr = $("#fias_grid").yiiGridView("getSelectedRows");

        if (sr.length === 0) {
            alert("Операция не может быть выполнена, выберите хотя бы один регион");
        }

        return sr;
    }

    function increaseRegionsProgress(progress_bar, full_length) {
        var current_length = progress_bar.attr("aria-valuenow");
        var prev_length = Math.round(full_length * current_length / 100);
        var new_length = prev_length + 1;
        var new_progress = Math.round(new_length * 100 / full_length);
        progress_bar
            .css("width", new_progress + "%")
            .text(new_progress + "%")
            .attr("aria-valuenow", new_progress);
    }

    function showAlert(message, className) {
        $('.fias-update-wrapper')
            .show()
            .find('.alert-wrapper')
            .show()
            .prepend('<div class="alert ' + className + '">' + message + '</div>');
    }

    $("#fias_update_btn").click(function () {
        var sr = getSelectedRegions();
        $('.fias-update-wrapper')
            .show()
            .find('.progress-bar')
            .width(0)
            .text("0%")
            .attr("aria-valuenow", 0);
        $('.fias-update-wrapper')
            .find('.alert-wrapper')
            .html('')
            .hide();

        if (sr.length > 0) {
            var promises = [];
            // ajax post request for each selected region
            for (var i = 0; i < sr.length; i++) {
                promises.push(
                    (function (region_number) {
                        return function () {
                            return $.post(
                                "/admin/fias/load-region",
                                {
                                    region_number: region_number
                                }
                            )
                                .done(function (data) {
                                    if (data.status) {
                                        increaseRegionsProgress($('.fias-update-wrapper .progress-bar'), sr.length);
                                    } else {
                                        let eventLocation = getCurrentLine();
                                        sendClientErrorToServer('error', data?.message, eventLocation);
                                        showAlert('Ошибка обновления региона ' + data.region + ' по причине: ' + data.message, 'alert-danger');
                                    }
                                })
                                .fail(function (e) {
                                    let eventLocation = getCurrentLine();
                                    sendClientErrorToServer('error', e?.responseText, eventLocation);
                                    showAlert('Не удалось обновить данные ФИАС', 'alert-danger');
                                })
                        }
                    })(sr[i])
                );
            }
            var seed = Promise.resolve(null);
            promises
                .reduce(function (a, b) {
                    return a.then(b);
                }, seed)
                .then(function () {
                    showAlert('Обновление данных ФИАС завершено', 'alert-info');
                });
        }
    });
});