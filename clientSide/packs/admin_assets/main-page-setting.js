$(function () {
    $('[data-toggle="tooltip"]').tooltip();
});

$('#sorted_element_form').on('submit', function () {
    const inputs = $('#sorted_element_form').find('[id^="mainpageinstruction"]');

    let K = 0;
    let result = {};
    let previousLiParent = null;
    for (let I = 0; I < inputs.length; I++) {
        const instructionResult = processInstructionInput(
            $(inputs[I]),
            previousLiParent,
            K
        );
        const sortedTrimName = instructionResult.sortedTrimName;
        previousLiParent = instructionResult.previousLiParent;
        K = instructionResult.K;

        result[sortedTrimName] = sortedTrimName;
    }

    $('[id*="-sortableelements"]').val(
        btoa(
            JSON.stringify(result)
        )
    );
    return true;
});

function processInstructionInput(input, previousLiParent, K) {
    // Структура имени `input`а формы выглядит как: <НазваниеМодели>[<идентификатор>][<атрибут_модели>]
    const sortedTrimRegex = /^(MainPageInstruction\w+\[\w+\])(\[\w+\])$/
    // Приведённое выше регулярное вырождение позволяет разбить имя `input`а на название модели и идентификатор
    // это нужно для заполнения скрытого поля `sortableElements`
    // содержащего последовательность списка макетов установленную пользователем

    const normalizeResult = normalizeNameForNewInstructionInput(
        input,
        previousLiParent,
        K
    );
    const name = normalizeResult.normalName;
    previousLiParent = normalizeResult.previousLiParent;
    K = normalizeResult.K;

    input.attr('name', name);
    return {
        sortedTrimName: name.replace(sortedTrimRegex, '$1'),
        previousLiParent,
        K
    };
}

function normalizeNameForNewInstructionInput(input, previousLiParent, K) {
    // Имена полей, которые были взяты из блока с шаблонами, отличаются от имён полей макета
    // отсутствием [<идентификатор>]
    const isOldFieldRegex = /^MainPageInstruction\w+\[\d+\]\[\w+\]$/
    // Приведённое выше регулярное вырождение определяет является ли данный `input` сохранённым ранее
    const replaceRegex = /^(MainPageInstruction\w+)(\[\w+\])$/
    // Приведённое выше регулярное вырождение позволяет разбить имя нового `input`а
    // так чтобы можно было его преобразовать к виду, который будет соответствовать старому `input`у

    let name = input.attr('name');
    const currentLiParent = input?.parents('li')[0];

    if (!isOldFieldRegex.test(name)) {
        if (previousLiParent != currentLiParent) {
            K++;
            previousLiParent = currentLiParent;
        }
        name = name.replace(replaceRegex, `$1[new${K}]$2`);
    }

    return {
        normalName: name,
        previousLiParent,
        K
    };
}