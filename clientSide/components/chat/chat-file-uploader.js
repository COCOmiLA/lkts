import '../../scss/chat-file-uploader.scss';
import {isEmptyValue} from "./common";
bindFileInputEvents();

function bindFileInputEvents() {
    $(".chat-file-uploader #old-file-upload")?.remove();

    $(".chat-file-uploader #file-upload").on("change", function () {
        updateFileInput(this);
    });
}

$(".chat-file-uploader .remove-btn").on("click", function () {
    eraseFileInput();
});

function eraseFileInput() {
    let fileInput = $(".chat-file-uploader #file-upload")[0];
    if (isEmptyValue(fileInput)) {
        return;
    }

    fileInput.value = "";
    if (fileInput.value) {
        // Это механизм "надёжного" сброса содержимого файл-инпута
        // для браузеров всех возрастов и типов
        fileInput.type = "text";
        fileInput.type = "file";
    }

    updateFileInput(fileInput);
}

/**
 * Старые браузер не поддерживают `structuredClone`
 * Эта функция создаёт копию инпута,
 * чтобы пользователь не мог повторно отправить тот же файл
 * пока предыдущая транзакция не завершилась
 *
 * @returns {void}
 */
function fileStructuredClone() {
    $(".chat-file-uploader #file-upload")?.attr("id", "old-file-upload");

    var newFileInput = document.createElement("input");
    newFileInput.setAttribute(
        "data-allowed_extensions",
        $(".chat-file-uploader #old-file-upload")?.data("allowed_extensions")
    );
    newFileInput.setAttribute("multiple", "");
    newFileInput.setAttribute("type", "file");
    newFileInput.setAttribute("name", "chat_file");
    newFileInput.setAttribute("id", "file-upload");

    var label = document.getElementById("label-for-file-upload");
    var chatFileUploader = document.getElementById("chat-file-uploader-for-history");
    chatFileUploader.insertBefore(newFileInput, label);
}

function updateFileInput(fileInput) {
    let errorField = $(".chat-file-uploader p");
    let removeBtn = $(".chat-file-uploader .remove-btn");
    let uploadFileName = $(".chat-file-uploader .upload-file-name");

    errorField.hide();

    if (fileInput.files.length < 1) {
        uploadFileName.text("");

        removeBtn.hide();
        uploadFileName.hide();

        return;
    }

    let files = [];
    let allowedExtensions = new RegExp($(fileInput)?.data("allowed_extensions"), "i");
    for (let I = 0; I < fileInput.files.length; I++) {
        const file = fileInput.files[I];

        if (!allowedExtensions.exec(file.name)) {
            eraseFileInput();
            errorField.show();
            return;
        }

        files.push(file.name);
    }
    let formattedFilesString = files.join(", ");

    uploadFileName.text(formattedFilesString);

    removeBtn.show();
    uploadFileName.show();
}

export { bindFileInputEvents, fileStructuredClone, eraseFileInput, updateFileInput };
