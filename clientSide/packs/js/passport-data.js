$("body").on("submit", ".passport-form", function (e) {
    e.preventDefault();

    var passportSuccessFunction = function passportSuccessFunction(res) {
        $(".passport-modal").modal("hide");
        $.ajax({
            url: passportGridUrl,
            method: "GET",
            dataType: "html",
            success: function success(response) {
                $("#passport-grid").html(response);
            }
        });
        $.ajax({
            url: passportModalsUrl,
            method: "GET",
            dataType: "html",
            success: function success(response) {
                $("#passport-modals").html(response);
            }
        });

        $("#alert-missing-passport").hide();
    };

    var textForAjaxTooltip =
        typeof globalTextForAjaxTooltip === "undefined"
            ? "Запрос обрабатывается..."
            : globalTextForAjaxTooltip;
    ajaxSender(
        $(this).attr("action"),
        "POST",
        new FormData(this),
        $(this).find(".btn-save-passport"),
        passportSuccessFunction,
        null,
        textForAjaxTooltip,
        "top",
        {
            contentType: false,
            cache: false,
            processData: false
        }
    );
    return false;
});
