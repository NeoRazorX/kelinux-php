<?php

require_once 'model/ke_question.php';
require_once 'model/ke_answer.php';

class question extends ke_controller
{
   public $question;
   
   public function __construct()
   {
      parent::__construct('question', 'Pregunta');
   }
   
   protected function process()
   {
      if( isset($_GET['param1']) )
      {
         $this->question = new ke_question();
         $this->question = $this->question->get($_GET['param1']);
         if($this->question)
         {
            $this->title = $this->question->title();
            
            if( isset($_POST['respuesta']) ) /// añadimos una respuesta
            {
               $answer = new ke_answer();
               $answer->question_id = $this->question->id;
               $answer->set_text($_POST['respuesta']);
               if($this->user)
                  $answer->user_id = $this->user->id;
               
               if( $answer->save() )
               {
                  $this->new_message("Respuesta guardada correctamente");
                  
                  /// ¿generamos una notificación?
                  if($answer->user_id != $this->question->user_id)
                  {
                     $noti = new ke_notification();
                     $noti->user_id = $this->question->user_id;
                     $noti->link = $answer->url();
                     $noti->text = $answer->text;
                     $noti->save();
                  }
                  
                  /// actualizamos la pregunta
                  $this->question->get_answers(); /// actualiza el número de respuestas y el estado
                  if( !$this->question->save() )
                     $this->new_error("¡Imposible actualizar la pregunta!");
               }
               else
                  $this->new_error("¡Imposible guardar la respuesta!");
            }
            else if( isset($_POST['add_reward']) ) /// añadimos puntos a la recompensa
            {
               $this->template = FALSE; /// desactivamos el motor de templates
               if( $this->question->is_solved() )
                  echo "No puedes añadir recompensa a una pregunta solucionada.";
               else if( $this->user )
               {
                  if( $this->user->points > 0 )
                  {
                     if( $this->question->add_reward(1) )
                     {
                        if( $this->user->add_points(-1) )
                           echo "OK;".$this->question->reward.";".$this->user->points;
                        else
                           echo "¡Error al descontarte los puntos!";
                     }
                     else
                        echo "¡Error al modificar la pregunta!";
                  }
                  else
                     echo "No tienes suficientes puntos.";
               }
               else
                  echo "Debes iniciar sesión para poder votar.";
            }
            else if( isset($_POST['vote_answer']) ) /// votamos una respuesta
            {
               $this->template = FALSE; /// desactivamos el motor de templates
               if( $this->user )
               {
                  if( $this->user->points > 0 )
                  {
                     $answer = new ke_answer();
                     $answer = $answer->get($_POST['vote_answer']);
                     if($answer)
                     {
                        if( $answer->vote( intval($_POST['points']) ) )
                        {
                           if($answer->user AND intval($_POST['points']) > 0)
                              $answer->user->add_points(1); /// los votos positivos suman un punto para el autor
                           
                           if( $this->user->add_points(-1) ) /// votar cuesta 1 punto
                              echo "OK;".$answer->id.";".$answer->grade.";".$this->user->points;
                           else
                              echo "¡Error al descontarte los puntos!";
                        }
                        else
                           echo "¡Error al modificar la respuesta!";
                     }
                     else
                        echo "¡Respuesta no encontrada!";
                  }
                  else
                     echo "No tienes suficientes puntos.";
               }
               else
                  echo "Debes iniciar sesión para poder votar.";
            }
            else if( isset($_POST['mark_solution']) ) /// marcamos una respuesta como la solución
            {
               $this->template = FALSE; /// desactivamos el motor de templates
               if($this->user)
               {
                  /// solamente un administrador o el autor de la pregunta podrá modificarla
                  $continuar = FALSE;
                  if( $this->user->is_admin() )
                     $continuar = TRUE;
                  else if($this->question->user)
                  {
                     if($this->question->user_id == $this->user->user_id)
                        $continuar = TRUE;
                  }
                  
                  if($continuar)
                  {
                     $answer = new ke_answer();
                     $answer = $answer->get($_POST['mark_solution']);
                     if($answer)
                     {
                        if( $answer->vote( $this->question->reward ) )
                        {
                           if($answer->user)
                              $answer->user->add_points( $this->question->reward );
                           
                           if( $this->question->set_solved() )
                              echo "OK";
                           else
                              echo "¡Error al marcar la pregunta como solucionada!";
                        }
                        else
                           echo "¡Error al marcar la respuesta!";
                     }
                     else
                        echo "¡Respuesta no encontrada!";
                  }
                  else
                     echo "No tienes permiso para hacer esto.";
               }
               else
                  echo "Debes iniciar sesión";
            }
            
            $this->question->mark_as_readed();
         }
         else
            $this->new_error("¡Pregunta no encontrada!");
      }
      else
      {
         $this->question = FALSE;
         $this->new_error("¡Pregunta no encontrada!");
      }
   }
   
   public function url()
   {
      if($this->question)
         return $this->question->url();
      else
         return KE_PATH.'/question_list';
   }
   
   public function get_description()
   {
      if( $this->question )
         return $this->question->resume();
      else
         return $this->title;
   }
}

?>
