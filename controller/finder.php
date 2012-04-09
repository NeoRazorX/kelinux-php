<?php

require_once 'model/ke_question.php';

class finder extends ke_controller
{
   public $question;
   public $resultado;
   
   public function __construct()
   {
      parent::__construct('finder', 'Buscador');
   }
   
   protected function process()
   {
      $this->question = new ke_question();
      $this->resultado = $this->question->search($this->query);
   }
}

?>
