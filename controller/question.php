<?php
/*
 * This file is part of Kelinux-php.
 * Copyright (C) 2012  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

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
            $this->title = $this->question->title().' ['.$this->question->get_status().']';
            
            if( isset($_POST['respuesta']) ) /// añadimos una respuesta
            {
               $answer = new ke_answer();
               $answer->question_id = $this->question->id;
               $answer->num = ($this->question->num_answers+1);
               $answer->set_text($_POST['respuesta']);
               if($this->user)
                  $answer->user_id = $this->user->id;
               
               if( $answer->save() )
                  $this->new_message("Respuesta guardada correctamente");
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
                        {
                           echo "¡Error al descontarte los puntos!";
                           if( $this->is_db_history_enabled() )
                           {
                              foreach($this->db_history() as $h)
                                 echo "\n".$h;
                           }
                        }
                     }
                     else
                     {
                        echo "¡Error al modificar la pregunta!";
                        if( $this->is_db_history_enabled() )
                        {
                           foreach($this->db_history() as $h)
                              echo "\n".$h;
                        }
                     }
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
                           {
                              echo "¡Error al descontarte los puntos!";
                              if( $this->is_db_history_enabled() )
                              {
                                 foreach($this->db_history() as $h)
                                    echo "\n".$h;
                              }
                           }
                        }
                        else
                        {
                           echo "¡Error al modificar la respuesta!";
                           if( $this->is_db_history_enabled() )
                           {
                              foreach($this->db_history() as $h)
                                 echo "\n".$h;
                           }
                        }
                     }
                     else
                     {
                        echo "¡Respuesta no encontrada!";
                        if( $this->is_db_history_enabled() )
                        {
                           foreach($this->db_history() as $h)
                              echo "\n".$h;
                        }
                     }
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
                     if($this->question->user_id == $this->user->id)
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
                           {
                              echo "¡Error al marcar la pregunta como solucionada!";
                              if( $this->is_db_history_enabled() )
                              {
                                 foreach($this->db_history() as $h)
                                    echo "\n".$h;
                              }
                           }
                        }
                        else
                        {
                           echo "¡Error al marcar la respuesta!";
                           if( $this->is_db_history_enabled() )
                           {
                              foreach($this->db_history() as $h)
                                 echo "\n".$h;
                           }
                        }
                     }
                     else
                     {
                        echo "¡Respuesta no encontrada!";
                        if( $this->is_db_history_enabled() )
                        {
                           foreach($this->db_history() as $h)
                              echo "\n".$h;
                        }
                     }
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
         return KE_PATH.'question_list';
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
