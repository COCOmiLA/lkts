$(function() {
    $(document).on('change', '.select-widget-not-found', function() {
        const elem = $(this);
        if (this.checked) {
            $('#' + elem.attr('data-block-id-not-found')).show();
            $('#' + elem.attr('data-block-id-not-found')).find('input, select').prop('disabled', false);
            $('#' + elem.attr('data-block-id-not-found')).find('input, select').attr('data-skip_validation', 0);

            $('#' + elem.attr('data-block-id-found')).hide();
            $('#' + elem.attr('data-block-id-found')).find('input, select').prop('disabled', true);
            $('#' + elem.attr('data-block-id-found')).find('input, select').attr('data-skip_validation', 1);
        } else {
            $('#' + elem.attr('data-block-id-not-found')).hide();
            $('#' + elem.attr('data-block-id-not-found')).find('input, select').prop('disabled', true);
            $('#' + elem.attr('data-block-id-not-found')).find('input, select').attr('data-skip_validation', 1);

            $('#' + elem.attr('data-block-id-found')).show();
            $('#' + elem.attr('data-block-id-found')).find('input, select').prop('disabled', false);
            $('#' + elem.attr('data-block-id-found')).find('input, select').attr('data-skip_validation', 0);
        }
    });

    $(document).on('click', '.approve-modal-btn', function(e) {
        e.preventDefault();
        const elem = $(this);
        $('.contractor-location-not-found').each(function() {
            setNotFound($(this));
        })
        $('#' + elem.attr('data-modal-id')).modal('show');
    });

    $(document).on('click', '.approve-new-contractor-btn', function(e) {
        e.preventDefault();

        const elem = $(this);
        const foundBlockId = elem.attr('data-found-block-id');
        const approveBlockId = elem.attr('data-approve-block-id');
        const contractor_type_ref_uid = $('#' + approveBlockId + ' :input[name="approve_contractor_type_ref_uid"]').val();
        
        if (!contractor_type_ref_uid) {            
            $('#' + approveBlockId + ' .form-group.approve-contractor-type').addClass('has-error');
            return;
        }

        const data = {
            subdivsion_code: $('#' + approveBlockId + ' :input[name="approve_contractor_subdivision_code"]').val(),
            contractor_name: $('#' + approveBlockId + ' :input[name="approve_contractor_name"]').val(),
            contractor_type_ref_uid: contractor_type_ref_uid,
            approve_contractor_location_code: $('#' + approveBlockId + ' :input[name="approve_contractor_location_code"]').val(),
            approve_contractor_location_name: $('#' + approveBlockId + ' :input[name="approve_contractor_location_name"]').val(),
            approve_contractor_location_not_found: +$('#' + approveBlockId + ' :input[name="approve_contractor_location_not_found"]').is(':checked'),
            approve_contractor_id: $('#' + approveBlockId + ' :input[name="approve_contractor_id"]').val()
        };

        $('#' + approveBlockId + ' .contractor-approve-error').first().hide();
        $('#' + approveBlockId + ' .form-group.approve-contractor-type').removeClass('has-error');

        let successFunc = function(data) {
            if (!data.status) {
                $('#' + approveBlockId + ' .contractor-approve-error').first().show();
                return;
            }

            const selectId = '#' + elem.attr('data-target-input-id');
            addSelectOptionIfNotExists(selectId, data);

            // закрытие модального окна подтверждения и показ сообщения о необходимости сохранить данные формы
            $('#' + elem.attr('data-modal-id')).modal('hide');
            $('#' + foundBlockId + ' .contractor-approve-state').show();
        }

        let errorFunc = function(data) {
            $('#' + approveBlockId + ' .contractor-approve-error').first().show();
            return;
        }

        ajaxSender(
            approveContractorUrl,
            "POST",
            data,
            elem,
            successFunc,
            errorFunc,
            approveContractorTextForAjaxTooltip
        );
    });

    $(document).on('click', '.bind-existing-contractor-btn', function(e) {
        e.preventDefault();
        
        const elem = $(this);
        const foundBlockId = elem.attr('data-found-block-id');
        const bindBlockId = '#' + elem.attr('data-bind-block-id');
        const selectId = '#' + elem.attr('data-target-input-id');
        const selectApprove = $('#' + elem.attr('data-from-input-id'));
        const approvedData = selectApprove.select2('data');

        $(bindBlockId + ' .contractor-bind-error').first().hide();

        if (!approvedData.length) {
            $(bindBlockId + ' .contractor-bind-error').first().show();
            return;
        }

        data = {
            id: selectApprove.val(),
            text: approvedData[0].text
        };
        addSelectOptionIfNotExists(selectId, data);
        
        // закрытие модального окна подтверждения и показ сообщения о необходимости сохранить данные формы
        $('#' + elem.attr('data-modal-id')).modal('hide');
        $('#' + foundBlockId + ' .contractor-approve-state').show();
    })

    // фиксит скролл после закрытия второго модального окна
    $(document).on('hidden.bs.modal', '.modal', function() {
        $('.modal:visible').length && $(document.body).addClass('modal-open');
    });

    $(document).on('change', '.contractor-location-not-found', function() {
        setNotFound($(this));
    });

    $('.contractor-location-not-found').each(function() {
        setNotFound($(this));
    });

    $(document).on('click', '.close-approve-contractor-modal', function(e) {        
        $(this).closest('.modal').modal('hide');
    });

    $('.approve-contractor-type-field').on('change', function() {
        if ($(this).val()) {
            $('.approve-contractor-type-field').parents('.form-group').removeClass('has-error');
        }
    });
});


function addSelectOptionIfNotExists(selectId, data)
{
    if ($(selectId).find("option[value='" + data.id + "']").length) {
        $(selectId).val(data.id).trigger('change');
    } else { 
        // Create a DOM Option and pre-select by default
        const newOption = new Option(data.text, data.id, true, true);
        // Append it to the select
        $(selectId).append(newOption).trigger('change');
    } 
}

function setNotFound(elem)
{
    if (elem.is(":checked")) {
        $('#' + elem.attr('data-block-id') + ' .location-found')
            .hide()
            .find('input, select').prop('disabled', true);

        $('#' + elem.attr('data-block-id') + ' .location-not-found')
            .show()
            .find('input, select').prop('disabled', false);
    } else {
        $('#' + elem.attr('data-block-id') + ' .location-found')
            .show()
            .find('input, select').prop('disabled', false);

        $('#' + elem.attr('data-block-id') + ' .location-not-found')
            .hide()
            .find('input, select').prop('disabled', true);
    }
}