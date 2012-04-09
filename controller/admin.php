<?php

class admin extends ke_controller
{
   public $acceso;

   public function __construct()
   {
      parent::__construct('admin', 'Administracion');
   }
   
   protected function process()
   {
      $this->acceso = FALSE;
      
      if( $this->user )
      {
         if( $this->user->is_admin() )
         {
            $this->acceso = TRUE;
            
            if( isset($_GET['param1']) )
            {
               if($_GET['param1'] == 'enable_db_history')
                  $this->enable_db_history(TRUE);
               else if($_GET['param1'] == 'disable_db_history')
                  $this->enable_db_history(FALSE);
               else if($_GET['param1'] == 'clean_chat')
                  $this->chat->clean();
            }
         }
      }
      
      if( !$this->acceso )
         $this->new_error("¡Tu no puedes acceder aquí, listo!");
   }
}

?>
