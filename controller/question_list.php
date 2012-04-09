<?php

require_once 'model/ke_question.php';

class question_list extends ke_controller
{
   public $question;
   public $resultado;
   public $offset;
   
   public function __construct()
   {
      parent::__construct('question_list', 'Preguntas');
   }
   
   protected function process()
   {
      $this->question = new ke_question();
      
      if( isset($_GET['param2']) )
         $this->offset = intval($_GET['param2']);
      else
         $this->offset = 0;
      
      $this->resultado = $this->question->all($this->offset);
   }
   
   public function anterior_url()
   {
      $url = '';
      if($this->offset > 0)
         $url = $this->url()."/created/".($this->offset-KE_ITEM_LIMIT);
      return $url;
   }
   
   public function siguiente_url()
   {
      $url = '';
      if(count($this->resultado) == KE_ITEM_LIMIT)
         $url = $this->url()."/created/".($this->offset+KE_ITEM_LIMIT);
      return $url;
   }
}

?>
