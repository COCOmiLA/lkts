function checkMinimalScore(formName, id, attribute) {
    var field = "";

    if (id.length > 0) {
        field = $("#".concat(formName, "-").concat(id, "-").concat(attribute));
    } else {
        field = $("#".concat(formName, "-").concat(attribute));
    }

    if (field.val().length < 1) {
        return 0;
    }

    if (parseInt(field.val()) < 0) {
        return -1;
    } else if (parseInt(field.val()) > 100) {
        return 1;
    }

    return 0;
}

$(function() {
    $("#ege-attachment").on("afterValidate", function (event, messages, errorAttributes) {
        if (errorAttributes.length) {
            errorAttributes.forEach(function(errorAttribute) {
                let collapsible_area = $(errorAttribute.input).closest('.collapse');
                if (!collapsible_area.hasClass('show')) {
                    collapsible_area.collapse('show');
                }
            });
        }
    });
});

window.checkMinimalScore = checkMinimalScore;
