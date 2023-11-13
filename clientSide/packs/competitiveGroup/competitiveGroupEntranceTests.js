$(function () {
    cleanAllHidden();
    changeAllMinScore();
    toggleAllRelatedButtonsFunc();
    $(".mainInput").on('change', function () {
        parallelClick($(this));
    });
});

// $(".uncheckable-radio:not(.disabled_permanently):not('.active')").on("click", function () {
//     if (!$(this).hasClass("active")) {
//         $(this)
//             .parents('.exam_form_buttons')
//             .find("label.active:not(.disabled_permanently)")
//             .button('toggle');
//     }
// });

function cleanAllHidden() {
    const removableHidden = $(".removableHidden");

    if (removableHidden.length > 0) {
        for (let index = 0; index < removableHidden.length; index++) {
            const element = removableHidden[index];

            cleanHidden($(element));
        }
    }
}

function cleanHidden($this) {
    const name = $this.attr("name");

    $this.parent().find(`input:hidden[name="${name}"]`).remove();
}

function toggleAllRelatedButtonsFunc() {
    const radioListLike = $(".radio_list_like");

    if (radioListLike.length > 0) {
        for (let index = 0; index < radioListLike.length; index++) {
            const element = radioListLike[index];

            toggleRelatedButtons($(element));
            parallelClick($(element));
        }
    }
}

function toggleRelatedButtons($this) {
    const data = $this.data("buttons");
    const trParent = $this.closest("tr");

    let isDisabled = $this.is(":disabled");

    if (!isDisabled) { isDisabled = !$this.is(":checked"); }

    trParent
        .find(`#${data}`)
        .find("input:not(.disabled_permanently)")
        .prop("disabled", isDisabled);
    if (isDisabled) {
        trParent
            .find(`#${data}`)
            .find("label:not(.disabled_permanently)")
            .addClass("disabled");
    } else {
        trParent
            .find(`#${data}`)
            .find("label:not(.disabled_permanently)")
            .removeClass("disabled");
    }
}

function changeAllMinScore() {
    const examFormButtons = $(".exam_form_buttons");

    if (examFormButtons.length > 0) {
        for (let index = 0; index < examFormButtons.length; index++) {
            const element = examFormButtons[index];

            changeMinScore($(element));
        }
    }
}

function changeMinScore($this) {
    const scores = $this.data("scores");
    const scoreSelector = $this.data("score_selector");

    const selectedButton = $this.find("input").filter(function () {
        return $(this).is(":checked");
    });

    const scoreIndex = parseInt(selectedButton.val());
    $this.closest("tr").find(`#${scoreSelector}`).text(scores[scoreIndex]);
}

function parallelClick($this) {
    const children = $(`#${$this.data("children_id")}`);

    if (children.length > 0) {
        children.prop("checked", $this.is(":checked"));
    }
}

window.changeMinScore = changeMinScore;
window.toggleAllRelatedButtons = toggleAllRelatedButtonsFunc;