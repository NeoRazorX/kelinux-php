<?php

require_once 'core/ke_tools.php';

class help extends ke_controller
{
   public $tools;

   public function __construct()
   {
      parent::__construct('help', 'Ayuda');
   }
   
   protected function process()
   {
      $this->tools = new ke_tools();
      if( isset($_POST['bbcode']) )
      {
         $this->template = FALSE; /// desactivamos el motor de plantillas
         echo $this->tools->var2html($_POST['bbcode']);
      }
   }
}

?>
