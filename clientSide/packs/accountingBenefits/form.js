//// Этот хитрый скрипт служит для управления чекбоксами "Преимущественное право"/"Льгота". Поскольку для одного и тоже кода может быть несколько состояний specific_law
function updateCheckbox(number) {
  //// Сначала парсим хитрый ключ словаря "ХХХХ_YY_Z", где:
  //// --- ХХХХ это guid льготы/спец_отметки
  //// --- YY флаг: specmark - SpecialMark, priv - Privilege
  //// --- Z указывает на "преимущественное право"
  var value = $("#lgot_id_".concat(number)).val();
  value = value.split("_")[2]; //// поведение описанное ниже мне не понятно, но я его просто воспроизвёл, как того требовала система

  if (value == 1) {
    $(".field-specific_law_".concat(number)).find($('input[type="checkbox"]')).prop("checked", true);
    $(".field-specific_law_".concat(number)).find($('input[type="checkbox"]')).prop("disabled", true);
    $(".field-concession_low_".concat(number)).find($('input[type="checkbox"]')).prop("checked", false);
    $(".field-concession_low_".concat(number)).find($('input[type="checkbox"]')).prop("disabled", true);
  } else if (value == 0) {
    $(".field-specific_law_".concat(number)).find($('input[type="checkbox"]')).prop("checked", false);
    $(".field-specific_law_".concat(number)).find($('input[type="checkbox"]')).prop("disabled", true);
    $(".field-concession_low_".concat(number)).find($('input[type="checkbox"]')).prop("checked", true);
    $(".field-concession_low_".concat(number)).find($('input[type="checkbox"]')).prop("disabled", false);
  } else {
    $(".field-specific_law_".concat(number)).find($('input[type="checkbox"]')).prop("checked", false);
    $(".field-concession_low_".concat(number)).find($('input[type="checkbox"]')).prop("checked", false);
    $(".field-specific_law_".concat(number)).find($('input[type="checkbox"]')).prop("disabled", false);
    $(".field-concession_low_".concat(number)).find($('input[type="checkbox"]')).prop("disabled", false);
  }
}

$('.lgot-code').change(function () {
  updateCheckbox($(this).data('number'));
});
$('.lgot-code').each(function () {
  updateCheckbox($(this).data('number'));
});