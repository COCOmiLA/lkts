$("body").on("submit", ".education-save-form", function (e) {
    e.preventDefault();

    var educationSuccessFunction = function educationSuccessFunction(res) {
        if (isFirstEducation) {
            setTimeout(function () {
                location.reload();
            }, 0);
        }
        $(".education-modal").modal("hide");
        $.pjax.reload({
            container: "#education-data-container",
            async: false,
        });
    };

    var textForAjaxTooltip =
        typeof globalTextForAjaxTooltip === "undefined"
            ? "Запрос обрабатывается..."
            : globalTextForAjaxTooltip;
    ajaxSender(
        $(this).attr("action"),
        "POST",
        new FormData(this),
        $(this).find(".btn-save-education"),
        educationSuccessFunction,
        null,
        textForAjaxTooltip,
        "top",
        {
            contentType: false,
            cache: false,
            processData: false,
        }
    );
    return false;
});
