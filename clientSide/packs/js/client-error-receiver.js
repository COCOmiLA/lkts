import getCurrentLine from 'get-current-line'

window.addEventListener("error", (event) => {
    sendErrorEventToServer(event);
});

/**
 * @param {JSON} log 
 */
function sendLogToServer(log) {
    try {
        $.ajax({
            url: "/client-error-receiver",
            type: "POST",
            data: log,
        })
            .done(function () {
                console.error(log);
            })
            .fail(function (response) {
                console.error({
                    clientError: log,
                    receiverFailResponse: response
                })
            });
    } catch (error) {
        console.error({
            clientError: log,
            receiverError: error
        });
    }
}

/**
 * @param {ErrorEvent} event 
 *
 * @returns {JSON}
 */
function buildLogFromErrorEvent(event) {
    return {
        url: window.location.href,
        type: event?.type,
        colno: event?.colno,
        lineno: event?.lineno,
        message: event?.message,
        fileName: event?.filename,
        errorMessage: event?.error?.message
    }
}

/**
 * @param {ErrorEvent} event 
 */
function sendErrorEventToServer(event) {
    const log = buildLogFromErrorEvent(event);

    sendLogToServer(log);
}

/**
 * @param {string} type
 * @param {string} message
 * @param {Location} eventLocation
 *
 * @returns {JSON}
 */
function buildLogFromClientError(type, message, eventLocation) {
    return {
        url: window.location.href,
        type: type,
        lineno: eventLocation.line,
        message: message,
        fileName: eventLocation.file,
        errorMessage: `Метод: «${eventLocation.method}», в котором возникла ошибка`
    }
}

/**
 * @param {string} type
 * @param {string} message
 * @param {Location} eventLocation
 */
export default function sendClientErrorToServer(type, message, eventLocation) {
    const log = buildLogFromClientError(type, message, eventLocation);

    sendLogToServer(log);
}

window.sendClientErrorToServer = sendClientErrorToServer;
window.getCurrentLine = getCurrentLine;