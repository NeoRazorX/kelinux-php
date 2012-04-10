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
