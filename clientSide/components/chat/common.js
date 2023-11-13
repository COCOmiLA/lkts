import ManagerChat from "./manager-chat";
import Chat from "./chat";
import getCurrentLine from 'get-current-line';
import sendClientErrorToServer from '../../packs/js/client-error-receiver.js';

const periodicUpdateChatPeopleListInterval = [];
const chats = {};

/**
 * Функция проверяет пустое ли к ней пришло значение
 *
 * @param {mixed} value
 *
 * @returns {boolean}
 */
function isEmptyValue(value) {
    return (
        typeof value === "undefined" ||
        value === "" ||
        value === null ||
        value === undefined ||
        (value === null || value === void 0 ? void 0 : value.length) === 0
    );
}

function periodicUpdateChatPeopleList() {
    updateChatPeopleList();

    initPeriodicUpdateChatPeopleList();
}

function initPeriodicUpdateChatPeopleList() {
    periodicUpdateChatPeopleListInterval.push(
        setTimeout(periodicUpdateChatPeopleList, updateRequestInterval)
    );
}

/**
 * @returns {void}
 */
function clearPeriodicUpdateChatPeopleList() {
    while (periodicUpdateChatPeopleListInterval.length) {
        const element = periodicUpdateChatPeopleListInterval.pop();

        clearTimeout(element);
    }
}

/**
 * функция связи списка людей и событий
 *
 * @returns {void}
 */
function bindEventsForSelectBtn() {
    $(".chat-select-btn").click(function (e) {
        let destinationId = $(this)?.data("destination_id");
        let chatId = $(this)?.data("chat_id");
        if (isEmptyValue(destinationId)) {
            return;
        }

        if (isEmptyValue(chats[destinationId])) {
            // `ChatClass` - Это переменная которая зависит от того какое представление будет использоваться для отображения чата
            if (ChatClass === "ManagerChat") {
                chats[destinationId] = new ManagerChat(destinationId);
            } else if (ChatClass === "Chat") {
                chats[destinationId] = new Chat(destinationId);
            }
        }
        let chat = chats[destinationId];
        chat.init();
        chat.chatId = chatId;

        let needScrollToBottom = false;
        if (!$(this)?.hasClass("active")) {
            chat.startLoading();
            chat.$textarea.prop("disabled", true);
            chat.$fileUploader.hide();

            needScrollToBottom = true;
        }

        chat.$chatSelectBtn.removeClass("active");
        $(this)?.addClass("active");

        let emptyChat = $(".empty-chat-history");
        $.ajax({
            type: "POST",
            url: openChatUrl,
            data: {
                chatId: chat.chatId,
                destinationId: destinationId,
            },
            beforeSend: function () {
                clearPeriodicUpdateChatPeopleList();
            },
        })
            .done(function (response) {
                emptyChat.hide();
                chat.$button.show();
                chat.$textarea.show();
                chat.$textarea.prop("disabled", false);
                chat.$fileUploader.show();
                chat.$textarea.focus();

                chat.chatId = response?.chatId;
                chat.init();
                $(this)?.data("chat_id", chat.chatId);

                chat.refreshChatHistory(response?.history);
                chat.renderChatHeader(response?.header);

                if (needScrollToBottom) {
                    chat.scrollToBottom();
                }
            })
            .fail(function () {
                emptyChat.removeClass("loading");
                emptyChat.append(createNewChatError);
            })
            .always(function () {
                initPeriodicUpdateChatPeopleList();
            });
    });
}

/**
 * Функция обновления списка пользователей
 *
 * @returns {void}
 */
function updateChatPeopleList() {
    $.ajax({
        type: "GET",
        url: updateChatPeopleListUrl,
    })
        .done(function (response) {
            let chatPeopleList = $(".chat-people-list");

            let chat = null;
            let activeChat = chatPeopleList.find(".active");
            let destinationId = activeChat?.data("destination_id");
            if (!isEmptyValue(destinationId) && !isEmptyValue(chats[destinationId])) {
                chat = chats[destinationId];
                chats[destinationId] = null;
            }

            chatPeopleList.empty();
            chatPeopleList.html(response);
            bindEventsForSelectBtn();

            if (!isEmptyValue(chat)) {
                chats[
                    chatPeopleList.find(`[data-chat_id="${chat.chatId}"]`)?.data("destination_id")
                ] = chat;
                chatPeopleList.find(`[data-chat_id="${chat.chatId}"]`)?.addClass("active");
                chatPeopleList.find(`[data-chat_id="${chat.chatId}"]`)[0]?.click();
            }
        })
        .fail(function (response) {
            let eventLocation = getCurrentLine();
            sendClientErrorToServer('error', response?.responseText, eventLocation);
        });
}

export {
    clearPeriodicUpdateChatPeopleList,
    updateChatPeopleList,
    periodicUpdateChatPeopleList,
    initPeriodicUpdateChatPeopleList,
    chats,
    bindEventsForSelectBtn,
    isEmptyValue,
};
