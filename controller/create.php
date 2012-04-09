<?php

require_once 'model/ke_question.php';

class create extends ke_controller
{
   public $question;
   
   public function __construct()
   {
      parent::__construct('create', 'Crear pregunta');
   }
   
   protected function process()
   {
      if( isset($_POST['tipo']) )
      {
         if($_POST['tipo'] == 'pregunta')
         {
            $this->question = new ke_question();
            $this->question->set_text($_POST['pregunta']);
            if($this->user)
            {
               $this->question->user_id = $this->user->id;
               $this->user->add_points(1);
            }
            
            if( $this->question->save() )
            {
               if( isset($_POST['community']) )
               {
                  $cq = new ke_community_question();
                  $cq->question_id = $this->question->id;
                  foreach($_POST['community'] as $cid)
                  {
                     $cq->community_id = $cid;
                     $cq->save();
                  }
               }
               header('location: '.$this->question->url());
            }
            else
               $this->new_error("¡Error al guardar la pregunta!");
         }
      }
   }
}

?>
