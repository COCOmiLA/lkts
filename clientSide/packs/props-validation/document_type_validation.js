import getCurrentLine from 'get-current-line';
import sendClientErrorToServer from '../../packs/js/client-error-receiver.js';

function setupValidationRules(target) {
    const element = $(target);
    const document_root = element.parents('.document-root');
    const document_type_id_option_attr = element.attr('data-document_type_id_option_attr');
    let document_type_id = null;
    if (typeof document_type_id_option_attr !== typeof undefined && document_type_id_option_attr !== false) {
        document_type_id = element.find(":selected").attr(document_type_id_option_attr);
    } else {
        document_type_id = element.val();
    }
    return $.post('/site/document-type-rules', {
        document_type_id
    })
        .done(function (prop_settings) {
            if (!prop_settings) {
                return;
            }
            for (const [attribute, rules] of Object.entries(prop_settings)) {
                const required = rules.required;
                const used = rules.used;
                const prop_elem = document_root.find('[data-one-s-attribute-name="' + attribute + '"]');
                const prop_elem_form_group = prop_elem.parents('.form-group');
                prop_elem.attr('data-is_required', +required);

                if (required) {
                    prop_elem_form_group.addClass('required').find('label').addClass('has-star');
                } else {
                    prop_elem_form_group.removeClass('required').find('label').removeClass('has-star');
                }

                if (used) {
                    prop_elem_form_group.show().find(':input').prop("disabled", false);
                } else {
                    prop_elem_form_group.hide().find(':input').prop("disabled", true);
                }
            }
        }).fail(function (e) {
            let eventLocation = getCurrentLine();
            sendClientErrorToServer('error', e?.responseText, eventLocation);
        });
}

$(document).ready(function () {
    $(document).on('change', '.document-root [data-document_type_input]', function (e) {
        setupValidationRules(e.target);
    });
    $('.document-root [data-document_type_input]').each(function () {
        setupValidationRules(this);
    });
});