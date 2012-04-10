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

class admin extends ke_controller
{
   public $acceso;

   public function __construct()
   {
      parent::__construct('admin', 'Administracion');
   }
   
   protected function process()
   {
      $this->acceso = FALSE;
      
      if( $this->user )
      {
         if( $this->user->is_admin() )
         {
            $this->acceso = TRUE;
            
            if( isset($_GET['param1']) )
            {
               if($_GET['param1'] == 'enable_db_history')
                  $this->enable_db_history(TRUE);
               else if($_GET['param1'] == 'disable_db_history')
                  $this->enable_db_history(FALSE);
               else if($_GET['param1'] == 'clean_chat')
                  $this->chat->clean();
            }
         }
      }
      
      if( !$this->acceso )
         $this->new_error("¡Tu no puedes acceder aquí, listo!");
   }
}

?>
