<?php

require_once 'model/ke_question.php';

class main_page extends ke_controller
{
   public $question;
   public $unreaded;

   public function __construct()
   {
      parent::__construct('main_page', 'Portada');
   }
   
   protected function process()
   {
      $this->question = new ke_question();
      $this->unreaded = array();
      foreach($this->question->all() as $u)
      {
         if( !$u->is_readed() )
            $this->unreaded[] = $u;
      }
   }
}

?>
