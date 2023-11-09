import {bindFileInputEvents, eraseFileInput, fileStructuredClone} from './chat-file-uploader'
import './chat-search'
import {
    clearPeriodicUpdateChatPeopleList,
    initPeriodicUpdateChatPeopleList,
    isEmptyValue,
    updateChatPeopleList
} from "./common";

export default class Chat {
    static INCOMING_TEMPLATE = "incoming-template";

    constructor(chatId) {
        this.chatId = chatId;

        this.filesToSend = "";
        this.messageToSend = "";
    }

    init() {
        this.cacheDOM();
        this.bindEvents();
        this.renderChatHistory();
    }

    cacheDOM() {
        this.$chatHeader = $(".chat-header");
        this.$button = $("#send-btn-message");
        this.$chatHistory = $(".chat-history");
        this.$textarea = $("#message-to-send");
        this.$chatSelectBtn = $(".chat-select-btn");
        this.$chatPeopleList = $(".people-list");
        this.$fileUploader = $(".chat-file-uploader");
        this.$chatHistoryList = this.$chatHistory.find("ul");
    }

    bindEvents() {
        this.$button.off();
        this.$button.on("click", this.addMessage.bind(this));

        this.$textarea.off();
        this.$textarea.on("keyup", this.addMessageByEnterBtn.bind(this));
    }

    /**
     * Функция включения анимации загрузки
     *
     * @returns {void}
     */
    startLoading() {
        let emptyChat = $(".empty-chat-history");

        if (emptyChat.length < 1) {
            this.$chatHistoryList?.empty();
            this.$chatHistoryList?.append("<li class='empty-chat-history d-flex justify-content-center align-items-center'></li>");

            emptyChat = $(".empty-chat-history");
        }

        emptyChat.empty();
        emptyChat.addClass("loading");
        this.renderChatHeader();
    }

    /**
     * Отрисовка заголовка чата
     *
     * @param {object|null} response - ответ сервера в формате JSON
     *
     * @returns {void}
     */
    renderChatHeader(response = null) {
        this.$chatHeader?.empty();

        if (response) {
            this.$chatHeader?.append(response);
        }
    }

    templateCompile(template, data) {
        const pattern = /{{\s*(\w+?)\s*}}/g; // {{property}}
        return template.replace(pattern, (_, token) => data[token] || "");
    }

    renderChatHistory(senderType) {
        if (
            (this.messageToSend.trim() !== "" || this.filesToSend.length > 0) &&
            !isEmptyValue(senderType)
        ) {
            let messageContext = {};
            if (this.messageToSend.trim() !== "") {
                messageContext = this.renderChatHistoryMessage(senderType);
            }

            let fileContexts = [];
            if (this.filesToSend.length > 0) {
                fileContexts = this.renderChatHistoryFile(senderType);
            }

            this.scrollToBottom();

            if (senderType === Chat.INCOMING_TEMPLATE) {
                eraseFileInput();
                this.$textarea.val("");

                this.sendMessage(messageContext, fileContexts);
            }
        }
    }

    renderChatHistoryMessage(senderType) {
        let template = $(`#${senderType}-message`).html();
        let context = {
            messageOutput: this.messageToSend.trim(),
            time: this.getCurrentDateTime(),
            messageUid: this.getRandomUid(),
        };
        this.$chatHistoryList.append(this.templateCompile(template, context));

        return context;
    }

    renderChatHistoryFile(senderType) {
        let contexts = [];
        if (this.filesToSend.length > 0) {
            let template = $(`#${senderType}-file`).html();

            for (let I = 0; I < this.filesToSend.length; I++) {
                const file = this.filesToSend[I];

                const context = {
                    time: this.getCurrentDateTime(),
                    fileUid: this.getRandomUid(),
                    fileName: file.name,
                };

                this.$chatHistoryList.append(this.templateCompile(template, context));

                contexts.push(context);
            }
        }

        return contexts;
    }

    /**
     * Функция генерации случайного идентификатора
     *
     * @returns {string}
     */
    getRandomUid() {
        return `${Math.random().toString(36).substring(2, 15)}-${Date.now()}-${Math.random()
            .toString(36)
            .substring(2, 15)}`;
    }

    /**
     * Инкрементами счётчика сообщений
     *
     * @param {int} delta
     *
     * @returns {void}
     */
    incrementMessageCounter(delta = 1) {
        let counter = $(".chat-num-messages span");

        if (isEmptyValue(counter?.text())) {
            counter?.text("1");
        }

        counter?.html(parseInt(counter?.text()) + delta);
    }

    /**
     * Функция отправки сообщения
     *
     * @param {object} messageContext - контекст сообщения
     * @param {array}  fileContexts - контексты файлов
     *
     * @returns {void}
     */
    sendMessage(messageContext, fileContexts) {
        let chat = this;

        let formData = new FormData();
        if (chat.filesToSend.length > 0) {
            for (let I = 0; I < chat.filesToSend.length; I++) {
                const file = chat.filesToSend[I];

                formData.append(`file[${I}]`, file, file.name);
            }
        }
        formData.append("chatId", this.chatId);
        formData.append("usersIds", JSON.stringify(this.getUsersIds(), null, 2));
        formData.append("fileContexts", JSON.stringify(fileContexts, null, 2));
        formData.append("messageContext", JSON.stringify(messageContext, null, 2));

        let totalSendMessageCount = chat.filesToSend.length;
        if (!isEmptyValue(messageContext?.messageOutput)) {
            totalSendMessageCount++;
        }

        $.ajax({
            url: sendMessageUrl,
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,

            beforeSend: function () {
                clearPeriodicUpdateChatPeopleList();
            },
        })
            .done(function (response) {
                chat.chatId = response.chatId;
                if (response.blobsUid.length > 0) {
                    chat.changeBlobStatus(response.blobsUid, "sending", "fa fa-check success");
                }

                bindFileInputEvents();
                chat.incrementMessageCounter(totalSendMessageCount);
                updateChatPeopleList();
            })
            .fail(function (response) {
                // TODO: добавить эвент превращающий клик в повторную отправку
                if (response.responseJSON.blobsUid.length > 0) {
                    chat.changeBlobStatus(
                        response?.responseJSON?.blobsUid,
                        "sending",
                        "fa fa-exclamation-triangle error"
                    );
                }
            })
            .always(function () {
                initPeriodicUpdateChatPeopleList();
            });
    }

    changeBlobStatus(blobsUid, removeClass, addClass) {
        for (let I = 0; I < blobsUid.length; I++) {
            const blobUid = blobsUid[I];
            let blobStatus = $(`#${blobUid}`)?.find(".message-status");
            if (blobStatus) {
                blobStatus.removeClass(removeClass);
                blobStatus.addClass(addClass);
            }
        }
    }

    /**
     * Функция получения идентификаторов пользователей
     *
     * @returns {array} - массив идентификаторов пользователей
     */
    getUsersIds() {
        let destinationUserId = this.$chatHeader?.find(".chat-about")?.data("destination_user_id");

        return [destinationUserId, currentUserId];
    }

    addMessage() {
        this.filesToSend = "";
        const filesTemp = $(".chat-file-uploader #file-upload")[0]?.files;
        if (!isEmptyValue(filesTemp)) {
            this.filesToSend = filesTemp;
            fileStructuredClone();
        }
        this.messageToSend = this.$textarea.val();
        this.renderChatHistory(Chat.INCOMING_TEMPLATE);
    }

    addMessageByEnterBtn(event) {
        // enter was pressed
        if (event.keyCode === 13) {
            this.addMessage();
        }
    }

    scrollToBottom() {
        this.$chatHistory.scrollTop(this.$chatHistory[0]?.scrollHeight);
    }

    getCurrentTime() {
        return new Date().toLocaleTimeString().replace(/([\d]+:[\d]{2})(:[\d]{2})(.*)/, "$1$3");
    }

    getCurrentDateTime() {
        let time = this.getCurrentTime();
        let date = this.formatDate(new Date());
        return `${date} ${time}`;
    }

    getRandomItem(arr) {
        return arr[Math.floor(Math.random() * arr.length)];
    }

    /**
     * Функция перерисовки истории чата
     *
     * @param {string} refreshedHistory
     *
     * @returns {void}
     */
    refreshChatHistory(refreshedHistory) {
        this.$chatHistoryList.empty();
        this.$chatHistoryList.html(refreshedHistory);
    }

    /**
     * Дополняет входящий номер до 2х символов
     * заполняя пустоту нулями (`0`)
     *
     * @param {string} num
     *
     * @returns {string}
     */
    padTo2Digits(num) {
        return this.padStart(num.toString(), 2, "0");
    }

    /**
     * В IE11 нет функции `String.prototype.padStart`
     * Придётся заменять её в ручную
     *
     * @param {string} originString
     * @param {int} targetLength
     * @param {string} padString
     *
     * @returns {string}
     */
    padStart(originString, targetLength, padString) {
        targetLength = targetLength >> 0;
        padString = String(typeof padString !== "undefined" ? padString : " ");
        if (originString.length > targetLength) {
            return String(originString);
        } else {
            targetLength = targetLength - originString.length;
            if (targetLength > padString.length) {
                padString += padString.repeat(targetLength / padString.length);
            }
            return padString.slice(0, targetLength) + String(originString);
        }
    }

    /**
     * Генерирует строку даты формата 'd.m.Y'
     *
     * @param {Date} date
     *
     * @returns {string}
     */
    formatDate(date) {
        return [
            this.padTo2Digits(date.getDate()),
            this.padTo2Digits(date.getMonth() + 1),
            date.getFullYear(),
        ].join(".");
    }
}
