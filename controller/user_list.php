<?php

class user_list extends ke_controller
{
   public $suser;

   public function __construct()
   {
      parent::__construct('user_list', 'Usuarios');
   }
   
   protected function process()
   {
      $this->suser = new ke_user();
   }
}

?>
