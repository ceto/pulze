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

  if(array_key_exists('userFields', $_POST) && !empty($_POST['userFields'])) {
    $text_Fields.='<p><strong>Fields</strong>: ';
    foreach($_POST["userFields"] as $key => $value) {
      $text_Fields.= $value.', ';
    }
    $text_Fields.='</p>';
  }

  if(array_key_exists('userApps', $_POST) && !empty($_POST['userApps'])) {
    $text_Apps.='<p><strong>Apps</strong>: ';
    foreach($_POST["userApps"] as $key => $value) {
      $text_Apps.= $value.', ';
    }
    $text_Apps.='</p>';
  }

  if(array_key_exists('userEngines', $_POST) && !empty($_POST['userEngines'])) {
    $text_Engines.='<p><strong>Engines</strong>: ';
    foreach($_POST["userEngines"] as $key => $value) {
      $text_Engines.= $value.', ';
    }
    $text_Engines.='</p>';
  }





  // if(strlen($user_Name)<4) {
  //   $output = json_encode(array('type'=>'error', 'text' => 'Full name is required'));
  //   die($output);
  // }
  // if(!filter_var($user_Email, FILTER_VALIDATE_EMAIL)) {
  //   $output = json_encode(array('type'=>'error', 'text' => 'Valid E-mail is required'));
  //   die($output);
  // }
  // if(strlen($user_Message)<6) {
  //   $output = json_encode(array('type'=>'error', 'text' => '<strong>Message is too short</strong>'));
  //   die($output);
  // }

  $emailcontent = 'Name: '.$user_Name. "\r\n".'E-mail: '.$user_Email. "\r\n\n".'--'."\r\n".$user_Comment;


  $headers = 'From: '.$user_Email.'' . "\r\n" .
  'Reply-To: '.$user_Email.'' . "\r\n" .
  'X-Mailer: PHP/' . phpversion();

  $sentMail = @mail($to_Email, $subject, $emailcontent, $headers);

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