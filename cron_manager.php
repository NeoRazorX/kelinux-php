<?php

require_once 'config.php';
require_once 'core/ke_db.php';
require_once 'model/ke_notification.php';
require_once 'phpmailer/class.phpmailer.php';

$db = new ke_db();
if( $db->connect() )
{
   $noti = new ke_notification();
   foreach($noti->all2sendmail() as $n)
   {
      if( $n->get_user() )
      {
         $mail = new PHPMailer();
         $mail->IsSMTP();
         $mail->CharSet = 'UTF-8';
         $mail->SMTPAuth = TRUE;
         $mail->SMTPSecure = "ssl";
         $mail->Host = "smtp.gmail.com";
         $mail->Port = 465;
         $mail->Username = KE_GMAIL_USER;
         $mail->Password = KE_GMAIL_PASS;
         $mail->FromName = KE_NAME;
         $mail->From = KE_GMAIL;
         
         $mail->AddAddress($n->user->email, $n->user->nick);
         $mail->Subject = 'Notificación de '.KE_NAME;
         $mail->Body = $n->text."\n\nhttp://www.".KE_DOMAIN.$n->link;
         if( !$mail->Send() )
            echo $mail->ErrorInfo;
      }
      
      $n->sendmail = FALSE;
      $n->save();
   }
   
   $db->close();
}
else
   echo "¡Imposible conectar con la base de datps!";

?>
