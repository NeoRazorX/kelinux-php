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

require_once 'core/ke_cache.php';
require_once 'core/ke_tools.php';
require_once 'model/ke_user.php';
require_once 'model/ke_notification.php';

class ke_chat_line extends ke_tools
{
   public $date;
   public $user;
   public $nick;
   public $text;
   
   public function __construct($u, $t)
   {
      $this->date = Date('j-n-Y H:i:s');
      
      if($u)
      {
         $this->user = $u;
         $this->nick = $this->user->nick;
      }
      else
      {
         $this->user = NULL;
         $ip = explode('.', $_SERVER['REMOTE_ADDR']);
         $this->nick = 'Anónimo_'.$ip[0].$ip[1].'X'.$ip[3];
      }
      
      $this->text = $this->nohtml($t);
   }
   
   public function timesince()
   {
      return $this->var2timesince($this->date);
   }
   
   public function text2html()
   {
      return $this->var2html($this->text);
   }
   
   public function get_users_mentioned($exclude=FALSE)
   {
      $mentionlist = array();
      $user = new ke_user();
      foreach($user->all() as $u)
      {
         if(preg_match('/@'.$u->nick.'($|\z|\W)/i', $this->text) AND !in_array($u, $mentionlist) AND $u != $exclude)
            $mentionlist[] = $u;
      }
      return $mentionlist;
   }
}

class ke_chat extends ke_cache
{
   private $history;
   private $num_unread;
   
   public function __construct()
   {
      parent::__construct();
      $this->history = $this->get_array('chat_history');
   }
   
   public function save()
   {
      $this->set('chat_history', $this->history);
   }
   
   public function new_comment($user, $text)
   {
      $comment = new ke_chat_line($user, $text);
      
      /// buscamos menciones directas a usuarios
      foreach($comment->get_users_mentioned($user) as $u)
      {
         $noti = new ke_notification();
         $noti->user_id = $u->id;
         $noti->link = KE_PATH.'#chat';
         $noti->set_chat_mention($user, $text);
         $noti->save();
      }
      
      $this->history[] = $comment;
      $this->save();
   }
   
   public function get_history()
   {
      return array_reverse($this->history);
   }
   
   public function all_comments_readed()
   {
      setcookie('chat_comments_read_date', strtotime(Date('j-n-Y H:i:s')), time()+2592000, KE_PATH);
   }
   
   public function num_unreaded_comments()
   {
      if( !isset($this->num_unread) )
      {
         $this->num_unread = 0;
         if( isset($_COOKIE['chat_comments_read_date']) )
         {
            foreach($this->history as $c)
            {
               if(strtotime($c->date) > $_COOKIE['chat_comments_read_date'])
                  $this->num_unread += 1;
            }
         }
         else
            $this->num_unread = count($this->history);
      }
      return $this->num_unread;
   }
   
   public function clean()
   {
      unset($this->history);
      $this->history = array();
      $this->save();
   }
}

?>
