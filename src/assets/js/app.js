$(document).foundation();

$('.smartlabelled input, .smartlabelled textarea').on('change', function() {
  if ($(this).val()!=='') {
    $(this).addClass('filled');
  } else {
    $(this).removeClass('filled');

  }
});