<?php

class log_in extends ke_controller
{
   public $notifications;

   public function __construct()
   {
      parent::__construct('log_in', 'Iniciar sesion');
   }
   
   protected function process()
   {
      if( $this->user )
         $this->user->mark_all_notifications_readed();
      else if( isset($_POST['n_nick']) AND isset($_POST['n_email']) AND isset($_POST['n_password']) )
      {
         $this->user = new ke_user();
         if( !$this->user->set_nick($_POST['n_nick']) )
         {
            $this->new_error( $this->user->errors );
            $this->user = FALSE;
         }
         else if( !$this->user->set_email($_POST['n_email']) )
         {
            $this->new_error( $this->user->errors );
            $this->user = FALSE;
         }
         else if( !$this->user->set_password($_POST['n_password']) )
         {
            $this->new_error( $this->user->errors );
            $this->user = FALSE;
         }
         else
         {
            $this->user->new_log_key();
            if( $this->user->save() )
            {
               setcookie('user_id', $this->user->id, time()+31536000, KE_PATH);
               setcookie('log_key', $this->user->log_key, time()+31536000, KE_PATH);
            }
            else
            {
               $this->new_error("¡Error al guardar el usuario!".$this->user->errors);
               $this->user = FALSE;
            }
         }
      }
   }
}

?>
