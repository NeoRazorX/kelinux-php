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

class user extends ke_controller
{
   public $suser;
   public $resultado;
   public $offset;
   public $order;
   
   public function __construct()
   {
      parent::__construct('user', 'Usuario: ');
   }
   
   protected function process()
   {
      if( isset($_GET['param1']) )
      {
         $this->suser = new ke_user();
         $this->suser = $this->suser->get_by_nick($_GET['param1']);
         if($this->suser)
         {
            $this->title .= $this->suser->nick;
            
            if( isset($_GET['param2']) )
               $this->order = $_GET['param2'];
            else
               $this->order = 'updated';
            
            if( isset($_GET['param3']) )
               $this->offset = intval($_GET['param3']);
            else
               $this->offset = 0;
            
            $this->resultado = $this->suser->get_questions($this->offset, KE_ITEM_LIMIT, $this->order);
         }
         else
            $this->new_error("¡Usuario no encontrado!");
      }
      else
      {
         $this->suser = FALSE;
         $this->new_error("¡Usuario no encontrado!");
      }
   }
   
   public function url()
   {
      if($this->suser)
         return $this->suser->url();
      else
         return KE_PATH.'user_list';
   }

   public function anterior_url()
   {
      $url = '';
      if($this->offset > 0)
         $url = $this->url().'/'.$this->order.'/'.($this->offset-KE_ITEM_LIMIT);
      return $url;
   }
   
   public function siguiente_url()
   {
      $url = '';
      if(count($this->resultado) == KE_ITEM_LIMIT)
         $url = $this->url().'/'.$this->order.'/'.($this->offset+KE_ITEM_LIMIT);
      return $url;
   }
}

?>
