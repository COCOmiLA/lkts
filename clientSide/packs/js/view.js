$(document).ready(function () {
    $(".toggle-panel").click(function () {
        let specid = $(this).data("specid");

        if ($(this).hasClass("fa-caret-up")) {
            $(this).removeClass("fa-caret-up");
            $(this).addClass("fa-caret-down");
            $("#body-" + specid).hide();
        } else {
            $(this).removeClass("fa-caret-down");
            $(this).addClass("fa-caret-up");
            $("#body-" + specid).show();
        }
    });

    $('#return-to-moderate-button').click(function (e) {
        e.preventDefault();
        let remove_in_one_s = !!document.querySelector('#remove_in_one_s_checkbox').checked
        let url = new URL($(this).data('url-to-return'));
        url.searchParams.set('remove_from_one_s', (remove_in_one_s ? '1' : '0'));
        window.location.href = url.toString();

        return false;
    });
});