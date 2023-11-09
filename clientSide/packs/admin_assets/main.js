import getCurrentLine from 'get-current-line';
import sendClientErrorToServer from '../../packs/js/client-error-receiver.js';

$(() => {
    $('#timeline-errors > a').click(function () {
        $.get('/admin/timeline-event/get-latest-logs')
            .done((logs) => {
                const header = $(this).parents('#timeline-errors').find('.dropdown-header')
                // очистка старых строк
                header.siblings()
                    .filter(function () {
                        return !$(this).hasClass('dropdown-footer')
                    })
                    .remove()
                // перед первой строкой добавляем разделитель
                header.after(`<div class="dropdown-divider"></div>`)
                for (const logEntry of logs) {
                    header.after(`
                    <a class="dropdown-item" href="/admin/log/view?id=${logEntry.id}">
                        <i class="fa fa-warning ${logEntry.level === 1 ? 'text-red' : 'text-yellow'}"></i>
                        ${logEntry.category}
                    </a>
                    <div class="dropdown-divider"></div>
                `)
                }
            })
            .fail((err) => {
                let eventLocation = getCurrentLine();
                sendClientErrorToServer('error', err?.responseText, eventLocation);
            })
    })
})
