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

class ke_answer extends ke_model
{
   public $id;
   public $text;
   public $user_id;
   public $question_id;
   public $created;
   public $grade;
   public $num;
   public $user;
   
   public function __construct($a=FALSE)
   {
      parent::__construct('answers');
      if($a)
      {
         $this->id = $this->intval($a['id']);
         $this->text = $a['text'];
         $this->user_id = $this->intval($a['user_id']);
         $this->question_id = $this->intval($a['question_id']);
         $this->created = $a['created'];
         $this->grade = intval($a['grade']);
         $this->user = new ke_user();
         $this->user = $this->user->get($this->user_id);
      }
      else
      {
         $this->id = NULL;
         $this->text = '';
         $this->user_id = NULL;
         $this->question_id = NULL;
         $this->created = Date('j-n-Y H:i:s');
         $this->grade = 0;
         $this->user = FALSE;
      }
      $this->num = 1;
   }
   
   public function text2html()
   {
      return $this->var2html($this->text);
   }
   
   public function created_timesince()
   {
      return $this->var2timesince($this->created);
   }
   
   public function resume()
   {
      if(strlen($this->text) > 300)
         return substr($this->text, 0, 300)."...";
      else
         return $this->text;
   }

   public function url($full=FALSE)
   {
      if($full)
         return 'http://www.'.KE_DOMAIN.KE_PATH.'question/'.$this->question_id.'#'.$this->num;
      else
         return KE_PATH.'question/'.$this->question_id.'#'.$this->num;
   }
   
   public function set_text($t)
   {
      $this->text = $this->nohtml($t);
   }

   public function vote($p=1)
   {
      $this->grade += intval($p);
      return $this->save();
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
   
   public function get_numbers_mentioned()
   {
      $numlist = array();
      $num = ($this->num-1);
      while($num > 0)
      {
         if( preg_match('/@'.$num.'($|\z|\D)/', $this->text) )
            $numlist[] = $num;
         
         $num--;
      }
      return $numlist;
   }
   
   public function get($id)
   {
      if( isset($id) )
      {
         $a = $this->db->select("SELECT * FROM ".$this->table_name." WHERE id = '".$id."';");
         if($a)
            return new ke_answer($a[0]);
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
         $sql = "UPDATE ".$this->table_name." SET text = ".$this->var2str($this->text).",
            user_id = ".$this->var2str($this->user_id).", question_id = ".$this->var2str($this->question_id).",
            created = ".$this->var2str($this->created).", grade = ".$this->var2str($this->grade)."
            WHERE id = '".$this->id."';";
      }
      else
      {
         $sql = "INSERT INTO ".$this->table_name." (text,user_id,question_id,created,grade) VALUES
            (".$this->var2str($this->text).",".$this->var2str($this->user_id).",".$this->var2str($this->question_id).",
            ".$this->var2str($this->created).",".$this->var2str($this->grade).");";
      }
      return $this->db->exec($sql);
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM ".$this->table_name." WHERE id = '".$this->id."';");
   }
   
   public function all_from_question($qid)
   {
      $alist = array();
      $answers = $this->db->select("SELECT * FROM ".$this->table_name." WHERE question_id = '".$qid."' ORDER BY created ASC;");
      if($answers)
      {
         $num = 1;
         foreach($answers as $a)
         {
            $na = new ke_answer($a);
            $na->num = $num;
            $num += 1;
            $alist[] = $na;
         }
      }
      return $alist;
   }
   
   public function avg_grade()
   {
      $grade = 0;
      $aux = $this->db->select("SELECT AVG(grade) as grade FROM ".$this->table_name.";");
      if($aux)
         $grade = floatval($aux[0]['grade']);
      return $grade;
   }
}

?>
