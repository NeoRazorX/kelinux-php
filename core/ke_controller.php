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

require_once 'ke_db.php';
require_once 'model/ke_user.php';
require_once 'model/ke_chat.php';
require_once 'model/ke_community.php';
require_once 'model/ke_question.php';

class ke_controller
{
   public $name;
   public $title;
   public $template;
   protected $db;
   public $messages;
   public $errors;
   private $uptime;
   public $query;
   public $user;
   public $chat;
   public $community;
   private $db_history_enabled;

   public function __construct($n='not_found', $t='page not found')
   {
      $this->name = $n;
      $this->title = $t;
      $this->template = $n;
      
      $tiempo = explode(' ', microtime());
      $this->uptime = $tiempo[1] + $tiempo[0];
      
      $this->db = new ke_db();
      if( $this->db->connect() )
      {
         if( isset($_POST['query']) )
            $this->query = $_POST['query'];
         else
            $this->query = '';
         
         $this->chat = new ke_chat();
         $this->community = new ke_community();
         
         if( isset($_GET['logout']) )
            $this->logout();
         else
            $this->login();
         
         $this->process();
      }
      else
         $this->new_error("¡Imposible conectar a la base de datos!");
   }
   
   private function login()
   {
      $this->user = FALSE;
      
      if( isset($_POST['l_email']) AND isset($_POST['l_password']) )
      {
         $suser = new ke_user();
         $suser = $suser->get_by_email($_POST['l_email']);
         if($suser)
         {
            if($suser->password == sha1($_POST['l_password']))
            {
               $this->user = $suser;
               $this->user->new_log_key();
               if( $this->user->save() )
               {
                  setcookie('user_id', $this->user->id, time()+31536000, KE_PATH);
                  setcookie('log_key', $this->user->log_key, time()+31536000, KE_PATH);
               }
               else
                  $this->new_error("¡Imposible guardar los datos del usuario!");
            }
            else
               $this->new_message("Contraseña incorrecta.");
         }
         else
            $this->new_message("El email ".$_POST['l_email']." no está asociado a ningún usuario.");
      }
      else if( isset($_COOKIE['user_id']) AND isset($_COOKIE['log_key']) )
      {
         $suser = new ke_user();
         $suser = $suser->get($_COOKIE['user_id']);
         if($suser)
         {
            if($suser->log_key == $_COOKIE['log_key'])
            {
               $this->user = $suser;
            }
            else
            {
               setcookie('user_id', $suser->id, time()-31536000, KE_PATH);
               setcookie('log_key', $suser->log_key, time()-31536000, KE_PATH);
               $this->new_error("Cookie inválida, debes volver a inciar sesión.");
            }
         }
         else
            $this->new_error("¿Tienes la cookie de un usuario que no existe?");
      }
   }
   
   private function logout()
   {
      $this->user = FALSE;
      setcookie('user_id', '', time()-31536000, KE_PATH);
      setcookie('log_key', '', time()-31536000, KE_PATH);
   }

   protected function process()
   {
      
   }
   
   public function get_app_name()
   {
      return KE_NAME;
   }

   public function get_path()
   {
      return KE_PATH;
   }
   
   public function get_analytics_id()
   {
      return KE_ANALYTICS_ID;
   }
   
   protected function new_message($msg)
   {
      $this->messages .= $msg . "\n";
   }
   
   protected function new_error($msg)
   {
      $this->errors .= $msg . "\n";
   }
   
   public function db_selects()
   {
      return $this->db->get_selects();
   }
   
   public function db_transactions()
   {
      return $this->db->get_transactions();
   }
   
   public function db_history()
   {
      return $this->db->get_history();
   }
   
   public function is_db_history_enabled()
   {
      if( $this->user )
      {
         if( $this->user->is_admin() )
         {
            if( isset($this->db_history_enabled) )
               return $this->db_history_enabled;
            else
               return isset($_COOKIE['db_history']);
         }
         else
            return FALSE;
      }
      else
         return FALSE;
   }
   
   protected function enable_db_history($v=FALSE)
   {
      if($v)
      {
         setcookie('db_history', TRUE, time()+86400, KE_PATH);
         $this->db_history_enabled = TRUE;
      }
      else
      {
         setcookie('db_history', FALSE, time()+86400, KE_PATH);
         $this->db_history_enabled = FALSE;
      }
   }
   
   public function num_solved_questions()
   {
      $q = new ke_question();
      return $q->num_solved();
   }

   public function duration()
   {
      $tiempo = explode(" ", microtime());
      $tiempo = $tiempo[1] + $tiempo[0];
      return(number_format($tiempo - $this->uptime, 3) . ' segundos');
   }
   
   public function url()
   {
      return KE_PATH.$this->name;
   }
   
   public function get_tags()
   {
      $tag = array();
      foreach($this->community->all() as $c)
         $tag[] = $c->name;
      return join(', ', $tag);
   }
   
   public function get_description()
   {
      return KE_NAME." es una red solical que tiene como misión solucionar
         todos los problemas que puedas tener con Linux, con cualquier
         distribución: Ubuntu, Linux Mint, Debian, Fedora, OpenSuSE ...
         y sin importar cuanto sepas de informática!";
   }
}

?>
