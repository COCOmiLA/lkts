import getCurrentLine from 'get-current-line';
import sendClientErrorToServer from '../../packs/js/client-error-receiver.js';

$(() => {
    $(".notification-drop .item").on('click', function () {
        $('.notification-drop').find('.notification-list').toggle();
    });
    updateUnreadCount();
    getMaxNotificationId(maxNotificationId);
});

let notificationDelay = notificationRequestInterval * 1000;

if (notificationDelay < 5000) {
    notificationDelay = 5000;
}

function renderNew() {
    $.ajax({
        type: "POST",
        url: '/notification-widget/new',
        dataType: "html",
        data: {
            max_id: maxNotificationId
        },
    })
        .done((data) => {
            $("#notification_list_container").prepend(data);
            getMaxNotificationId(maxNotificationId);
        })
        .fail((error) => {
            let eventLocation = getCurrentLine();
            sendClientErrorToServer('error', error?.responseText, eventLocation);
        })
}

function getMaxNotificationId(current_max_id) {
    $.ajax({
        type: "GET",
        url: '/notification-widget/max-id',
        dataType: "json",
    })
        .done((new_max_id) => {
            updateUnreadCount();
            if (new_max_id > current_max_id) {
                renderNew();
                maxNotificationId = new_max_id;
            } else {
                setTimeout(getMaxNotificationId, notificationDelay, maxNotificationId);
            }
        })
        .fail((error) => {
            let eventLocation = getCurrentLine();
            sendClientErrorToServer('error', error?.responseText, eventLocation);
        })
}

function updateUnreadCount() {
    $.ajax({
        type: "GET",
        url: '/notification-widget/unread-count',
        dataType: "json",
    })
        .done((data) => {
            if (data > 0) {
                $('#uread_notifications_count').show();
            } else {
                $('#uread_notifications_count').hide();
            }

            $('#uread_notifications_count').html(data);
        })
        .fail((error) => {
            let eventLocation = getCurrentLine();
            sendClientErrorToServer('error', error?.responseText, eventLocation);
        })
}

function readNotification(id) {
    $.ajax({
        type: "POST",
        url: '/notification-widget/read',
        dataType: "json",
        data: {
            id: id
        },
    })
        .done((data) => {
            if (data) {
                $('.list-group-item[data-id="' + id + '"]').removeClass('list-group-item-new');
                updateUnreadCount();
            }
        })
        .fail((error) => {
            let eventLocation = getCurrentLine();
            sendClientErrorToServer('error', error?.responseText, eventLocation);
        })
}

$(document).on('click', '.notification__expand-btn', function (e) {
    if ($(this).attr('aria-expanded') === 'true') {
        readNotification($(this).data('id'));
    }
});