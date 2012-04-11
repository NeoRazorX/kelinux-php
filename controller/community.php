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
   public $scommunity;
   
   public function __construct()
   {
      parent::__construct('community', 'Comunidad: ');
   }
   
   protected function process()
   {
      if( isset($_GET['param1']) )
      {
         $this->scommunity = $this->community->get_by_name($_GET['param1']);
         if($this->scommunity)
         {
            $this->title .= $this->scommunity->name;
            if( isset($_GET['param2']) )
            {
               if($_GET['param2'] == 'join')
               {
                  $this->scommunity->add_user( $this->user->id );
               }
               else if($_GET['param2'] == 'leave')
               {
                  $this->scommunity->rm_user( $this->user->id );
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
}

?>