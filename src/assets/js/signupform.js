 /*** Signup Form ******/
$(document).on('formvalid.zf.abide', function(ev,frm) {

  var user_name = $('input[name=name]').val();
  var user_email = $('input[name=email]').val();
  var user_coname = $('input[name=co_name]').val();
  var user_comment = $('textarea[name=comment]').val();

  var proceed = true;
  // if (user_name === '') {
  //   frm.foundation('addErrorClasses', $('#name'));
  //   frm.find('#name + label').addClass('is-invalid-label');
  //   frm.find('#name + label + .form-error').addClass('is-visible');
  //   proceed = false;
  // }

  //everything looks good! proceed...
  if (proceed) {
      //data to be sent to server
      var post_data = {
          'userName': user_name,
          'userEmail': user_email,
          'userConame': user_coname,
          'userComment': user_comment
      };

      //Ajax post data to server
      $.post( frm.attr('action'), post_data, function(response){
          var output = '';
          if (response.type === 'error') {
              output = '<div class="callout"><p>' + response.text + '</p></div>';
          }
          else {
              output = '<div class="callout"><p>' + response.text + '</p></div>';
              frm[0].reset();
              $('.formactions').hide();
          }

          $('#result').hide().html(output).slideDown();
      }, 'json');

  }

  //return false;
}).on('submit', function(ev) {
    ev.preventDefault();
});

// $('#signupform input, #signupform textarea').keyup(function(){
//   $('#result').slideUp();
// });
