import getCurrentLine from 'get-current-line';
import sendClientErrorToServer from '../../packs/js/client-error-receiver.js';
import _ from 'lodash';

const parse_selected_rows = (rows) => {
    return _.map(rows, (row) => {
        if (typeof row === 'string') {
            return JSON.parse(row);
        }
        return row;
    })
}
const change_permissions_selected_rows = async (state) => {
    const $resubmissionTable = $('#resubmission_management_users_table');

    const user_id_type_ids = $resubmissionTable.yiiGridView("getSelectedRows");
    if (!user_id_type_ids || !user_id_type_ids.length) {
        alert("Пожалуйста, выберите хотя бы одну строку");
    }
    try {
        const result = await $.post('/resubmission/change-permissions',
            {
                user_id_type_ids: parse_selected_rows(user_id_type_ids),
                permission: state
            })
        if (result.success) {
            // reload pjax
            $.pjax.reload({
                timeout: false,
                container: '#resubmission_management_users_table_pjax',
            });
        } else {
            alert(result.message);
        }
    } catch (e) {
        let eventLocation = getCurrentLine();
        sendClientErrorToServer('error', e?.stack, eventLocation);
    }
}
$(() => {
    $(document).on('click', '#allow_resubmit', (e) => {
        e.preventDefault();
        change_permissions_selected_rows(true);
    })
    $(document).on('click', '#disallow_resubmit', (e) => {
        e.preventDefault();
        change_permissions_selected_rows(false);
    })
})