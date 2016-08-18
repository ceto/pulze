<?php
  require_once 'phplib/swiftmailer/lib/swift_required.php';
  require_once '../../mail.conf.php';

  // Create the Transport
  $transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 587,'tls');
  $transport->setUsername($smtp_user);
  $transport->setPassword($smtp_pass);

  $mailer = Swift_Mailer::newInstance($transport);
?>
<?php
if($_POST) {
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
  $user_Noemployees = filter_var($_POST["userNoemployees"], FILTER_SANITIZE_STRING);
  $user_Comment = filter_var($_POST["userComment"], FILTER_SANITIZE_STRING);

  $user_Comment = str_replace("\&#39;", "'", $user_Comment);
  $user_Comment = str_replace("&#39;", "'", $user_Comment);


  $pulzemessage = '<p><strong>Name: </strong> '.$user_Name.'<br>'.
                      '<strong>Email: </strong> '.$user_Email.'<br>'.
                      '<strong>Company: </strong> '.$user_Coname.'<br>'.
                      '<strong>Number of Employees: </strong> '.$user_Noemployees.'<p><hr>';

  if(array_key_exists('userFields', $_POST) && !empty($_POST['userFields'])) {
    $text_Fields.='<p><strong>Fields:</strong> ';
    foreach($_POST["userFields"] as $key => $value) {
      $text_Fields.= $value.', ';
    }
    $text_Fields.='</p>';
    $pulzemessage.=$text_Fields;
  }

  if(array_key_exists('userApps', $_POST) && !empty($_POST['userApps'])) {
    $text_Apps.='<p><strong>Apps:</strong> ';
    foreach($_POST["userApps"] as $key => $value) {
      $text_Apps.= $value.', ';
    }
    $text_Apps.='</p>';
    $pulzemessage.=$text_Apps;
  }

  if(array_key_exists('userEngines', $_POST) && !empty($_POST['userEngines'])) {
    $text_Engines.='<p><strong>Engines:</strong> ';
    foreach($_POST["userEngines"] as $key => $value) {
      $text_Engines.= $value.', ';
    }
    $text_Engines.='</p>';
    $pulzemessage.=$text_Engines;
  }

  $pulzemessage .= '<p><strong>Comment: </strong><br>'.$user_Comment.'</p>';


  //Compose email to Pulze
  $pmessage = Swift_Message::newInstance();
  $pmessage->setSubject($email_subject);
  $pmessage->setFrom( array( $user_Email => $user_Name ) );
  $pmessage->setTo( array( $email_to => $email_toname ) );
  $pmessage->setBody($pulzemessage,'text/html');


  if ($presult = $mailer->send($pmessage) ) {
    $output = json_encode(array('type'=>'message', 'text' => '<strong>Dear '.$user_Name .'</strong><br>Your registration has been successfully sent. We will contact you very soon!'));
    //compose email to user
    $usermessage = '<p><strong>Dear '.$user_Name.'</strong></p>'.$emailresponse;

    $umessage = Swift_Message::newInstance();
    $umessage->setSubject($email_subject);
    $umessage->setFrom( array( $email_from => $email_fromname ) );
    $umessage->setTo( array( $user_Email => $user_Name ) );
    $umessage->setBody($usermessage,'text/html');
    $uresult = $mailer->send($umessage);
    die($output);
  } else {
    $output = json_encode(array('type'=>'error', 'text' => 'Failed to send message!'));
    die($output);
  }
}

?>