function disableTTLInput() {
    $("#token-ttl").prop("disabled", true);
}

function enableTTLInput() {
    $("#token-ttl").prop("disabled", false);
}

function checkConfirmEmailInput() {
    var val = $("#confirm-email").is(":checked");

    if (val) {
        enableTTLInput();
    } else {
        disableTTLInput();
    }
}

function checkAllowRememberMeInput() {
    if ($("#allow_remember_me").is(":checked")) {
        $("#remember_me_duration_container").show();
        $("#identity_cookie_duration").prop("disabled", false);
        $("#identity_cookie_duration_select").prop("disabled", false);
    } else {
        $("#remember_me_duration_container").hide();
        $("#identity_cookie_duration").prop("disabled", true);
        $("#identity_cookie_duration_select").prop("disabled", true);
    }
}

function checkRememberMeDuration() {
    var numOfSecondsInMonth = 2592000;
    var val = $('#identity_cookie_duration_select').val();
    var duration = parseInt(val);
    duration = duration || numOfSecondsInMonth;
    
    if (!val) {
        $('#identity_cookie_duration_block').show();
    } else {
        $('#identity_cookie_duration_block').hide();
    }
}

$(document).ready(function () {
    checkConfirmEmailInput();
    checkAllowRememberMeInput();
});

$("#confirm-email").change(function () {
    checkConfirmEmailInput();
});

$("#allow_remember_me").change(function () {
    checkAllowRememberMeInput();
});

$("#identity_cookie_duration_select").change(function() {
    checkRememberMeDuration();
});