<?php

class stats extends ke_controller
{
   public $question;
   public $answer;
   
   public function __construct()
   {
      parent::__construct('stats', 'Estadisticas');
   }
   
   protected function process()
   {
      $this->question = new ke_question();
      $this->answer = new ke_answer();
   }
}

?>
