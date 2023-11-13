function disciplineValidatorFunc(id) {
    var field = $(`#${id}`);

    if (field.is(":checked")) {
        return false;
    }

    var idPart = id.split("--")[0];
    var allRadio = $(`[id*="${idPart}"]`);

    if (allRadio.length > 0) {
        for (var index = 0; index < allRadio.length; index++) {
            var radio = $(allRadio[index]);

            if (!radio.is(":checked")) {
                continue;
            }

            if (!(radio.val().length < 1 || radio.val() == "0")) {
                return false;
            }
        }
    }
    return true;
}

window.disciplineValidator = disciplineValidatorFunc