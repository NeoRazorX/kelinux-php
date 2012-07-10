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

class community extends ke_controller
{
   public $offset;
   public $questions;
   public $scommunity;
   public $top_question;
   
   public function __construct()
   {
      parent::__construct('community', 'Comunidad ');
   }
   
   protected function process()
   {
      if( isset($_GET['param1']) )
      {
         $this->scommunity = $this->community->get_by_name($_GET['param1']);
         if($this->scommunity)
         {
            $this->title .= $this->scommunity->name . ' de '.KE_NAME;
            
            if( isset($_GET['param2']) )
               $this->offset = intval($_GET['param2']);
            else
            {
               $this->offset = 0;
               
               $topq = $this->scommunity->get_questions(0, TRUE);
               if($topq)
               {
                  if(count($topq) == 0)
                     $this->top_question = FALSE;
                  else
                     $this->top_question = $topq[rand(0, count($topq)-1)];
               }
            }
            
            $this->questions = $this->scommunity->get_questions($this->offset);
            
            if( $this->user )
            {
               if( isset($_POST['delete_community']) )
               {
                  if(intval($_POST['delete_community']) == $this->scommunity->id AND $this->user->is_admin())
                  {
                     if( $this->scommunity->delete() )
                     {
                        $this->new_message("Comunidad eliminada correctamente");
                        $this->scommunity = FALSE;
                     }
                     else
                        $this->new_error("¡Imposible eliminar la comunidad!");
                  }
               }
               else if( isset($_GET['param2']) )
               {
                  if($_GET['param2'] == 'join')
                  {
                     $this->scommunity->add_user( $this->user->id );
                     $this->log->new_line($this->user->nick.' se une a la comunidad '.$this->scommunity->name);
                  }
                  else if($_GET['param2'] == 'leave')
                  {
                     $this->scommunity->rm_user( $this->user->id );
                     $this->log->new_line($this->user->nick.' abandona la comunidad '.$this->scommunity->name);
                  }
               }
               else if( isset($_POST['edit_community']) )
               {
                  $this->scommunity->set_description($_POST['description']);
                  if( $this->scommunity->save() )
                     $this->new_message("Comunidad modificada correctamente");
                  else
                     $this->new_error("¡Imposible modificar la comunidad!");
               }
            }
         }
         else
            $this->new_error("¡Comunidad no encontrada!");
      }
      else
      {
         $this->scommunity = FALSE;
         $this->new_error("¡Comunidad no encontrada!");
      }
   }
   
   public function url()
   {
      if($this->scommunity)
         return $this->scommunity->url();
      else
         return KE_PATH.'community_list';
   }
   
   public function get_description()
   {
      if($this->scommunity)
         return $this->scommunity->description;
      else
         return $this->title;
   }
   
   public function anterior_url()
   {
      $url = '';
      if($this->offset > 0)
         $url = $this->url().'/'.($this->offset-KE_ITEM_LIMIT);
      return $url;
   }
   
   public function siguiente_url()
   {
      $url = '';
      if(count($this->questions) == KE_ITEM_LIMIT)
         $url = $this->url().'/'.($this->offset+KE_ITEM_LIMIT);
      return $url;
   }
}

?>
