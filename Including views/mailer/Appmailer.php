<?php
namespace mailer;

require_once 'class.phpmailer.php';
class Appmailer{
   
   protected $_mailer;
   
   public function __construct() {
      $mail             = new \PHPMailer(); // defaults to using php "mail()"
      $mail->IsSMTP(); // telling the class to use SendMail transport       
      //$mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
      $mail->SMTPAuth   = true;                  // enable SMTP authentication
      $mail->Host       = "smtp.gmail.com"; // sets the SMTP server
      $mail->Port       = 587;                    // set the SMTP port for the GMAIL server
      $mail->Username   = "cimba.invitation@gmail.com"; // SMTP account username
      $mail->Password   = 'CiMb@$nEo';
      $mail->SetFrom('noreplycimba@gmail.com', 'Cimba User Registration'); 
      $this->_mailer = $mail;
   }
   
   public function send($subject,$address,$message){    
      $this->_mailer->AddAddress($address);
      $this->_mailer->Subject    = $subject;
      $objUtilitiesFncs = new \utilities\CommonFunctions();
      
      // below variables are to be used for templates
      $salutation_message = $message['salutation_message'];
      $greeting_message = $message['greeting_message'];
      $message_content = $message['message_content'];
      // get required file contents in varible
      ob_start();
      require_once 'templates/ams_registeration.php';
      $body = ob_get_clean();
      $this->_mailer->MsgHTML($body);
      $this->_mailer->send();
   }
  
}

?>

