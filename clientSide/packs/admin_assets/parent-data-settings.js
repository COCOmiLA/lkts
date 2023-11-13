$(function() {
    checkAddressOptionInputs();
    checkPassportOptionInput();
});

$("#codesetting-require_parent_passport_data").change(function() {
    checkPassportOptionInput();
});

$('#codesetting-require_parent_address_data').change(function() {
    checkAddressOptionInputs();
});

function checkPassportOptionInput() {
    var val = $('#codesetting-require_parent_passport_data').val();
    if (val === '1') {
        $('#codesetting-hide_passport_data_block').val('0').trigger('change');
        $('#codesetting-hide_passport_data_block').prop('disabled', true);
    } else {
        $('#codesetting-hide_passport_data_block').prop('disabled', false);
    }
}
function checkAddressOptionInputs() {
    var val = $('#codesetting-require_parent_address_data').val();
    if (val === '1') {
        $('#codesetting-hide_address_data_block').val('0').trigger('change');
        $('#codesetting-hide_address_data_block').prop('disabled', true);
    } else {
        $('#codesetting-hide_address_data_block').prop('disabled', false);
    }
}