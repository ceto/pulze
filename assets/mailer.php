<?php
if($_POST) {
  $to_Email = "szabogabi@gmail.com";
  $subject = 'Pulze - Sign Up';

  if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {

    $output = json_encode(
    array(
      'type'=>'error',
      'text' => 'Request must come from Ajax'
    ));

    die($output);
  }

  if ( !isset($_POST["userName"]) || !isset($_POST["userEmail"]) ) {
    $output = json_encode(array('type'=>'error', 'text' => 'Marked fields are required.'));
    die($output);
  }
  $user_Name = filter_var($_POST["userName"], FILTER_SANITIZE_STRING);
  $user_Email = filter_var($_POST["userEmail"], FILTER_SANITIZE_EMAIL);
  $user_Coname = filter_var($_POST["userConame"], FILTER_SANITIZE_STRING);
  $user_Comment = filter_var($_POST["userComment"], FILTER_SANITIZE_STRING);

  $user_Comment = str_replace("\&#39;", "'", $user_Message);
  $user_Comment = str_replace("&#39;", "'", $user_Message);

  if(strlen($user_Name)<4) {
    $output = json_encode(array('type'=>'error', 'text' => 'Full name is required'));
    die($output);
  }
  if(!filter_var($user_Email, FILTER_VALIDATE_EMAIL)) {
    $output = json_encode(array('type'=>'error', 'text' => 'Valid E-mail is required'));
    die($output);
  }
  // if(strlen($user_Message)<6) {
  //   $output = json_encode(array('type'=>'error', 'text' => '<strong>Message is too short</strong>'));
  //   die($output);
  // }


  $headers = 'From: '.$user_Email.'' . "\r\n" .
  'Reply-To: '.$user_Email.'' . "\r\n" .
  'X-Mailer: PHP/' . phpversion();

  $sentMail = @mail($to_Email, $subject, 'Name: '.$user_Name. "\r\n". 'E-mail: '.$user_Email. "\r\n\n".'--'."\r\n".$user_Comment, $headers);

  if(!$sentMail) {
    $output = json_encode(array('type'=>'error', 'text' => 'Failed to send message!'));
    die($output);
  } else {

    $resp_headers = 'From: '.$to_Email.'' . "\r\n" .
    'Reply-To: '.$to_Email.'' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();
    @mail($user_Email, $subject, 'Name: '.$user_Name. "\r\n". 'E-mail: '.$user_Email. "\r\n\n".'--'."\r\n".$user_Comment, $resp_headers);

    $output = json_encode(array('type'=>'message', 'text' => '<strong>Dear '.$user_Name .'</strong><br>Your registration has been successfully sent. We will contact you very soon!'));
    die($output);
  }
}

?>