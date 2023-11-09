$(document).ready(function () {
  $("li.disabled > a").click(function () {
    return false;
  });
  
  /* раскоментировать если нужна функциональность окна подтверждения несохраненных изменений на странице */
  /*
  $("form").areYouSure({
      message: unsavedLeave,
  });
   */
});
$('[data-toggle="tooltip"]').tooltip();

$(document).ready(function () {
  $("body").tooltip({
    selector: '[data-toggle=tooltip]'
  }); 
  
  //todo: проверить используется ли такой селектор
  $("#make-application").click(function () {
    var selected = $("#education-form option:selected").val();

    if (selected == "1") {
      $(this).attr("href", "' . Url::toRoute(['bachelor/ege']) . '");
    } else {
      return false;
    }
  });
});