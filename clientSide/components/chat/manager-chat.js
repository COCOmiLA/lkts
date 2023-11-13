import Chat from './chat.js';
import {clearPeriodicUpdateChatPeopleList, initPeriodicUpdateChatPeopleList} from "./common";

export default class ManagerChat extends Chat {
    init() {
        super.init();

        this.bindEvents();
    }

    bindEvents() {
        super.bindEvents();

        this.bindChatEndEvents();
    }

    bindChatEndEvents() {
        $("#chat-end-btn").off();
        $("#chat-end-btn").on("click", this.chatEnd.bind(this));
    }

    chatEnd() {
        let url = $("#chat-end-btn").data("url");

        this.startLoading();
        this.$fileUploader.hide();
        this.$textarea.prop("disabled", true);
        this.$chatSelectBtn.removeClass("active");

        let emptyChat = $(".empty-chat-history");
        $.ajax({
            type: "POST",
            url: url,
            data: { chatId: this.chatId },
            beforeSend: function () {
                clearPeriodicUpdateChatPeopleList();
            },
        })
            .done(function () {
                document.location.reload();
            })
            .fail(function () {
                emptyChat.removeClass("loading");
                emptyChat.append(createNewChatError);
            })
            .always(function () {
                initPeriodicUpdateChatPeopleList();
            });
    }

    /**
     * {@inheritdoc}
     */
    renderChatHeader(response = null) {
        super.renderChatHeader(response);

        this.bindChatEndEvents();
    }
}
