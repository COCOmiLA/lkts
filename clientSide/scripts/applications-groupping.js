import getCurrentLine from "get-current-line";
import sendClientErrorToServer from "../packs/js/client-error-receiver";

export function checkForPossibleGroups() {
    $('#combined_cg_modal').find('#add-combined-group').on('click', function () {
        let specIds = $('#combined_cg_modal').data('specialities-to-add');
        localStorage.setItem("checkedSpecIds", specIds);
        $('#combined_cg_modal').modal('hide');
        addSpecialities(specIds.split(","));
    })

    $('#combined_cg_modal').find('#combined-group-dismiss').on('click', function () {
        let specIds = $('#combined_cg_modal').data('specialities-to-add');
        localStorage.setItem("checkedSpecIds", specIds);
    });

    checkAvailableParentSpecialities();
}

function addSpecialities(specIds) {
    $.post(
        addSpecialitiesUrl,
        {spec: specIds},
    )
        .done(function (data) {
                // reload page
                window.location.reload();
            }
        )
        .fail(function (e) {
            let eventLocation = getCurrentLine();
            sendClientErrorToServer('error', e?.responseText, eventLocation);
        })
}

function checkAvailableParentSpecialities() {
    $.post(
        getAvailableParentSpecialitiesUrl,
        {applicationId: getApplicationId()},
    )
        .done(function (data) {
            if (data && data.length > 0) {
                let specIds = data.map(x => x.id)
                let specIdsString = specIds.join(',');
                let checkedSpecIds = localStorage.getItem("checkedSpecIds")
                if (checkedSpecIds !== specIdsString) {
                    let html = renderMessage(data);
                    $('#combined_cg_modal').find('.modal-body').html(html);
                    $('#combined_cg_modal').data('specialities-to-add', specIdsString);
                    $('#combined_cg_modal').modal('show');
                }
            }
        })
        .fail(function (e) {
            let eventLocation = getCurrentLine();
            sendClientErrorToServer('error', e?.responseText, eventLocation);
        });
}

function getApplicationId() {
    return $('#combined_cg_modal').data('applicationid');
}

function renderMessage(data) {
    let html = '<div class="alert alert-info" role="alert">';
    html += '<h4 class="alert-heading">Внимание!</h4>';

    for (let i = 0; i < data.length; i++) {
        html += '<p>В списке конкурсов присутствуют конкурсы, ' +
            `соответствующие конкурсной группе по совмещенной квоте ${data[i].name}. ` +
            `Добавить конкурсную группу ${data[i].name} в список?</p>`;
    }
    html += '</div>';

    return html;
}
