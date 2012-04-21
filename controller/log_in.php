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

class log_in extends ke_controller
{
   public $notifications;

   public function __construct()
   {
      parent::__construct('log_in', 'Iniciar sesion');
   }
   
   protected function process()
   {
      /// ¿el usuario ha olvidado su contraseña?
      if( isset($_POST['password_forgotten']) )
      {
         $suser = new ke_user();
         $suser = $suser->get_by_email($_POST['password_forgotten']);
         if($suser)
         {
            $noti = new ke_notification();
            $noti->user_id = $suser->id;
            $noti->text = "Parece que tienes problemas para conectar, haz click en este enlace para iniciar sesión".
                    " -> http://www.".KE_DOMAIN.KE_PATH."log_in/".$suser->id.'/'.$suser->log_key;
            if( $noti->save() )
               $this->new_message("Se te enviará un email en breve con más instrucciones");
            else
               $this->new_error("¡Imposible continuar!");
         }
         else
            $this->new_error("El email ".$_POST['password_forgotten']." no está asociado a ninguna cuenta");
      }
      else if( isset($_GET['param1']) AND isset($_GET['param2']) )
      {
         /// se está iniciando sesión mediante un enlace que se ha recibido por email
         $suser = new ke_user();
         $suser = $suser->get($_GET['param1']);
         if($suser)
         {
            if($_GET['param2'] == $suser->log_key)
            {
               $this->user = $suser;
               setcookie('user_id', $this->user->id, time()+31536000, KE_PATH);
               setcookie('log_key', $this->user->log_key, time()+31536000, KE_PATH);
            }
            else
               $this->new_error("Datos incorrectos");
         }
         else
            $this->new_error("Usuario no encontrado");
      }
      
      if( $this->user )
      {
         $this->user->mark_all_notifications_readed();
         
         if( isset($_POST['edit_user']) )
         {
            if( isset($_POST['noemails']) )
               $this->user->no_emails = TRUE;
            else
               $this->user->no_emails = FALSE;
            
            if( isset($_POST['npassword']) AND isset($_POST['npassword2']) )
            {
               if($_POST['npassword'] == $_POST['npassword2'])
                  $this->user->password = sha1($_POST['npassword']);
               else
                  $this->new_error("¡Las contraseñas deben coincidir!");
            }
            
            if( $this->user->save() )
               $this->new_message("Datos de usuarios modificados correctamente.");
            else
               $this->new_error("¡Imposible modificar los datos de usuario!");
         }
      }
      else if( isset($_POST['n_nick']) AND isset($_POST['n_email']) AND isset($_POST['n_password']) )
      {
         $this->user = new ke_user();
         if( !$this->user->set_nick($_POST['n_nick']) )
         {
            $this->new_error( $this->user->errors );
            $this->user = FALSE;
         }
         else if( !$this->user->set_email($_POST['n_email']) )
         {
            $this->new_error( $this->user->errors );
            $this->user = FALSE;
         }
         else if( !$this->user->set_password($_POST['n_password']) )
         {
            $this->new_error( $this->user->errors );
            $this->user = FALSE;
         }
         else
         {
            $this->user->new_log_key();
            if( $this->user->save() )
            {
               setcookie('user_id', $this->user->id, time()+31536000, KE_PATH);
               setcookie('log_key', $this->user->log_key, time()+31536000, KE_PATH);
            }
            else
            {
               $this->new_error("¡Error al guardar el usuario!".$this->user->errors);
               $this->user = FALSE;
            }
         }
      }
   }
}

?>
