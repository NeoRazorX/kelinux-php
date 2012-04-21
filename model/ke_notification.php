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

require_once 'core/ke_model.php';
require_once 'model/ke_user.php';

class ke_notification extends ke_model
{
   public $id;
   public $user_id;
   public $date;
   public $text;
   public $link;
   public $sendmail; /// true -> hay que mandar un email
   public $readed;
   public $user;
   
   public function __construct($n=FALSE)
   {
      parent::__construct('notifications');
      if($n)
      {
         $this->id = $this->intval($n['id']);
         $this->user_id = $this->intval($n['user_id']);
         $this->date = $n['date'];
         $this->text = $n['text'];
         $this->link = $n['link'];
         $this->sendmail = ($n['sendmail'] == 1);
         $this->readed = ($n['readed'] == 1);
      }
      else
      {
         $this->id = NULL;
         $this->user_id = NULL;
         $this->date = Date('j-n-Y H:i:s');
         $this->text = '';
         $this->link = '';
         $this->sendmail = TRUE;
         $this->readed = FALSE;
      }
   }
   
   public function timesince()
   {
      return $this->var2timesince($this->date);
   }
   
   public function set_answer($user, $answer)
   {
      if( $user )
         $nick = $user->nick;
      else
         $nick = 'un usuario anónimo';
      $this->text = ucfirst($nick)." responde: '".$this->nobbcode($answer)."'.";
   }
   
   public function set_mention($user, $answer)
   {
      if( $user )
         $nick = $user->nick;
      else
         $nick = 'un usuario anónimo';
      $this->text = ucfirst($nick)." te ha mencionado diciendo: '".$this->nobbcode($answer)."'.";
   }
   
   public function set_chat_mention($user, $answer)
   {
      if( $user )
         $nick = $user->nick;
      else
         $nick = 'un usuario anónimo';
      $this->text = ucfirst($nick)." te ha mencionado en el chat diciendo: '".$this->nobbcode($answer)."'.";
   }
   
   public function get_user()
   {
      if( !isset($this->user) )
      {
         $u = new ke_user();
         $this->user = $u->get($this->user_id);
      }
      return $this->user;
   }

   public function get($id)
   {
      if( isset($id) )
      {
         $n = $this->db->select("SELECT * FROM ".$this->table_name." WHERE id = '".$id."';");
         if($n)
            return new ke_notification($n[0]);
         else
            return FALSE;
      }
      else
         return FALSE;
   }
   
   public function exists()
   {
      if( is_null($this->id) )
         return FALSE;
      else
         return $this->db->select("SELECT * FROM ".$this->table_name." WHERE id = '".$this->id."';");
   }
   
   public function save()
   {
      if( $this->exists() )
      {
         $sql = "UPDATE ".$this->table_name." SET user_id = ".$this->var2str($this->user_id).",
            date = ".$this->var2str($this->date).", text = ".$this->var2str($this->text).",
            link = ".$this->var2str($this->link).", sendmail = ".$this->var2str($this->sendmail).",
            readed = ".$this->var2str($this->readed)." WHERE id = '".$this->id."';";
      }
      else
      {
         $sql = "INSERT INTO ".$this->table_name." (user_id,date,text,link,sendmail,readed) VALUES
            (".$this->var2str($this->user_id).",".$this->var2str($this->date).",".$this->var2str($this->text).",
            ".$this->var2str($this->link).",".$this->var2str($this->sendmail).",".$this->var2str($this->readed).");";
      }
      return $this->db->exec("$sql");
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM ".$this->table_name." WHERE id = '".$this->id."';");
   }
   
   public function all_from_user($uid, $offset=0, $limit=KE_ITEM_LIMIT)
   {
      $nlist = array();
      $notis = $this->db->select_limit("SELECT * FROM ".$this->table_name." WHERE user_id = '".$uid."' ORDER BY date DESC", $offset, $limit);
      if($notis)
      {
         foreach($notis as $n)
            $nlist[] = new ke_notification($n);
      }
      return $nlist;
   }
   
   public function num_unreaded_from_user($uid)
   {
      $num = 0;
      $notis = $this->db->select("SELECT COUNT(*) as num FROM ".$this->table_name." WHERE user_id = '".$uid."' AND readed = FALSE;");
      if($notis)
         $num = intval( $notis[0]['num'] );
      return $num;
   }
   
   public function all2sendmail()
   {
      $nlist = array();
      $notis = $this->db->select("SELECT * FROM ".$this->table_name." WHERE sendmail = TRUE;");
      if($notis)
      {
         foreach($notis as $n)
            $nlist[] = new ke_notification($n);
      }
      return $nlist;
   }
   
   public function mark_as_readed_from_user($uid)
   {
      return $this->db->exec("UPDATE ".$this->table_name." SET readed = TRUE, sendmail = FALSE WHERE user_id = '".$uid."' AND readed = FALSE;");
   }
}

?>
