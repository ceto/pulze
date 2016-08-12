 /*** Signup Form ******/
$('#signupform').on('submit', function(ev,frm) {
  ev.preventDefault();
  //alert("Form is submitted, finally!");


  //get input field values
  var user_name = $('input[name=name]').val();
  var user_email = $('input[name=email]').val();
  var user_coname = $('input[name=co_name]').val();
  var user_comment = $('textarea[name=comment]').val();

  var proceed = true;
  if (user_name === '') {
      // $('#name').addClass('is-invalid-input');
      // $('#name').attr('data-invalid');
      // $('#name + label').addClass('is-invalid-label');
      // $('#name + label + .form-error').addClass('is-visible');
      proceed = false;
  }
  if (user_email === '') {
      // $('input[name=email]').addClass('is-invalid-input');
      // $('input[name=email]').attr('data-invalid');
      // $('input[name=email] + label').addClass('is-invalid-label');
      // $('input[name=email] + label + .form-error').addClass('is-visible');
      proceed = false;
  }


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
      $.post($('#signupform').attr('action'), post_data, function(response){
          var output = '';
          //load json data from server and output message
          if (response.type === 'error') {
              output = '<div class="callout"><p>' + response.text + '</p></div>';
          }
          else {

              output = '<div class="callout"><p>' + response.text + '</p></div>';

              //reset values in all input fields
              $('#signupform input').val('');
              $('#signupform textarea').val('');
              $('.formactions').hide();
          }

          $('#result').hide().html(output).slideDown();
      }, 'json');

  }

  return false;
});

$('#signupform input, #signupform textarea').keyup(function(){
  $('#result').slideUp();
});
