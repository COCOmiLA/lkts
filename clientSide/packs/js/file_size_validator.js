/**
 * Данный скрипт занимается проверкой и валидацией передаваемых файлов.
 * Основное назначение - не дать пользователю загрузить файлов общий объём которых
 * превышает максимальный объём передаваемых файлов
 */
$(window).on("load", function () {
    /**
     * ! Переменная *formId* объявлена в представлении `common\view\_file_size_validator.php`.
     * * В ней хранится ID той формы в которой необходимо проводить валидацию общего объёма файлов.
     */
    $("#" + formId).on("beforeValidate", function () {
        var totalSize = 0;
        var totalCount = 0;
        $('input[type="file"]').each(function () {
            if (this.files && this.files.length > 0) {
                totalCount += this.files.length;

                for (var index = 0; index < this.files.length; index++) {
                    totalSize += this.files[index].size;
                }
            }
        });
        /**
         * ! Переменная *uploadSizeLimit* объявлена в представлении `common\view\_file_size_validator.php`.
         * * В ней хранится максимальный размер для передаваемого файла.
         */
        if ((uploadSizeLimit && totalSize > uploadSizeLimit) || (totalCount > maxFileUploads)) {
            $(".file-size-validator").show();
            return false;
        }

        $(".file-size-validator").hide();
        return true;
    });
});