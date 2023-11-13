var receiver_selection = [];

$(document).on('submit', '#notification-form', function (e) {
  var form = document.getElementById('notification-form');
  
  if (!customValidation()) {
      return false;
  }
  
  var container = document.getElementById('hidden_inputs'); // удаляем если какие то элементы есть

  while (container.firstChild) {
    container.removeChild(container.lastChild);
  }

  // добавляем input'ы перед отправкой формы
  var fragment = document.createDocumentFragment();
  $('#notification-type').val().forEach(function (el) {
    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'notification_type[]';
    input.value = el;
    fragment.appendChild(input);
  });
  receiver_selection.forEach(function (el) {
    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'receiver_selection[]';
    input.value = el;
    fragment.appendChild(input);
  });
  container.appendChild(fragment);
  form.appendChild(container);
  return true;
});
$(document).on('change', '.receiverCheck', function () {
  var checked_receivers_count = $('.receiverCheck:checked').length;
  $('.receiver_count').html(checked_receivers_count);
  receiver_selection = $('#receiver-grid').yiiGridView('getSelectedRows');
});
$(document).on('click', '#send-to-all', function () {
  if ($(this).is(':checked')) {
    $('.receiver_count').html(all_receivers.length);
    $('.receiverCheck, .select-on-check-all').attr('disabled', true);
    receiver_selection = all_receivers;
  } else {
    $('.receiverCheck').trigger('change');
    $('.receiverCheck, .select-on-check-all').attr('disabled', false);
    receiver_selection = $('#receiver-grid').yiiGridView('getSelectedRows');
  }
});
$(document).on('change', '#notification-type', function () {});
$(document).on('hidden.bs.modal', '#notification-modal', function () {
  // очистка ошибок при закрытии окна отправки
  $('#notification-errors').hide();
});

$(document).on('pjax:complete', function (e) {
  $('.receiverCheck').trigger('change');
});

function customValidation()
{
    var list = $('#notification-errors').find('ul');
    list.empty();
    
    if ($('#notification-type').val().length === 0) {
        list.append('<li>' + no_types_text + '</li>');
    }

    if (receiver_selection.length === 0) {
        list.append('<li>' + no_receivers_text + '</li>');
    }
    
    if ($('#notification-errors ul li').length > 0) {
        $('#notification-errors').show();
        return false;
    } else {
        $('#notification-errors').hide();
        return true;
    }
}