$("#custom-legend").fadeIn("fast");

$("#loading").hide();

$(document).on("pjax:send", function () {
    $("#loading").show();
    $("#chart_graphics").hide();
});

$(document).on("pjax:complete", function () {
    $("#loading").hide();
    $("#chart_graphics").show();
});

/**
 * Данный кусочек скрипта предназначен для замены кнопки "сабмит". При нажатии на кнопку
 * "Показать" из формы выше собирается POST запрос на отправку в "/student/chart",
 * как будто это настоящая кнопка "сабмит". Эот всё ради не перегрузки "DepDrop".
 */
$(document).on("click", "button#_chart_button", function () {
    var data = $("form#_chart_form").serializeArray();
    $.pjax({
        data: data,
        push: true,
        type: "POST",
        replace: false,
        timeout: 10000,
        scrollTo: false,
        url: "/chart/index",
        container: "#_chart",
    });
    return false;
});
