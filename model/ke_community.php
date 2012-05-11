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
require_once 'model/ke_question.php';

class ke_community_user extends ke_model
{
   public $user_id;
   public $community_id;
   
   public function __construct($c=FALSE)
   {
      parent::__construct('user_community');
      if( $c )
      {
         $this->user_id = $c['users_id'];
         $this->community_id = $c['communities_id'];
      }
      else
      {
         $this->user_id = NULL;
         $this->community_id = NULL;
      }
   }
   
   public function exists()
   {
      if(is_null($this->user_id) OR is_null($this->community_id) )
         return FALSE;
      else
         return $this->db->select("SELECT * FROM ".$this->table_name." WHERE users_id = '".$this->user_id."'
            AND communities_id = '".$this->community_id."';");
   }
   
   public function save()
   {
      if( $this->exists() )
         return TRUE;
      else
         return $this->db->exec("INSERT INTO ".$this->table_name." (users_id,communities_id) VALUES
            (".$this->var2str($this->user_id).",".$this->var2str($this->community_id).");");
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM ".$this->table_name." WHERE users_id = '".$this->user_id."'
            AND communities_id = '".$this->community_id."';");
   }
   
   public function all_from_user($uid, $offset=0, $limit=KE_ITEM_LIMIT)
   {
      $commidlist = array();
      if( isset($uid) )
      {
         $commids = $this->db->select_limit("SELECT * FROM ".$this->table_name." WHERE users_id = '".$uid."'", $offset, $limit);
         if($commids)
         {
            foreach($commids as $c)
               $commidlist[] = new ke_community_user($c);
         }
      }
      return $commidlist;
   }
   
   public function all_from_community($cid, $offset=0, $limit=KE_ITEM_LIMIT)
   {
      $uidlist = array();
      if( isset($cid) )
      {
         $uids = $this->db->select_limit("SELECT * FROM ".$this->table_name." WHERE communities_id = '".$cid."'", $offset, $limit);
         if($uids)
         {
            foreach($uids as $u)
               $uidlist[] = new ke_community_user($u);
         }
      }
      return $uidlist;
   }
}

class ke_community_question extends ke_model
{
   public $question_id;
   public $community_id;

   public function __construct($c=FALSE)
   {
      parent::__construct('community_question');
      if($c)
      {
         $this->question_id = $c['questions_id'];
         $this->community_id = $c['communities_id'];
      }
      else
      {
         $this->question_id = NULL;
         $this->community_id = NULL;
      }
   }
   
   public function exists()
   {
      if(is_null($this->question_id) OR is_null($this->community_id) )
         return FALSE;
      else
         return $this->db->select("SELECT * FROM ".$this->table_name." WHERE questions_id = '".$this->question_id."'
            AND communities_id = '".$this->community_id."';");
   }
   
   public function save()
   {
      if( $this->exists() )
         return TRUE;
      else
         return $this->db->exec("INSERT INTO ".$this->table_name." (questions_id,communities_id) VALUES
            (".$this->var2str($this->question_id).",".$this->var2str($this->community_id).");");
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM ".$this->table_name." WHERE questions_id = '".$this->question_id."'
            AND communities_id = '".$this->community_id."';");
   }
   
   public function all_from_question($qid, $offset=0, $limit=KE_ITEM_LIMIT)
   {
      $commidlist = array();
      if( isset($qid) )
      {
         $cq = $this->db->select_limit("SELECT * FROM ".$this->table_name." WHERE questions_id = '".$qid."'", $offset, $limit);
         if($cq)
         {
            foreach($cq as $c)
            {
               $commidlist[] = new ke_community_question($c);
            }
         }
      }
      return $commidlist;
   }
   
   public function all_from_community($cid, $offset=0, $limit=KE_ITEM_LIMIT)
   {
      $qidlist = array();
      if( isset($cid) )
      {
         $cq = $this->db->select_limit("SELECT * FROM ".$this->table_name." WHERE communities_id = '".$cid."'", $offset, $limit);
         if($cq)
         {
            foreach($cq as $c)
               $qidlist[] = new ke_community_question($c);
         }
      }
      return $qidlist;
   }
}

class ke_community extends ke_model
{
   public $id;
   public $name;
   public $description;
   public $created;
   public $num_users;
   
   public function __construct($c=FALSE)
   {
      parent::__construct('communities');
      if($c)
      {
         $this->id = $this->intval($c['id']);
         $this->name = $c['name'];
         $this->description = $c['description'];
         $this->created = $c['created'];
         $this->num_users = intval($c['num_users']);
      }
      else
      {
         $this->id = NULL;
         $this->name = '';
         $this->description = '';
         $this->created = Date('j-n-Y H:i:s');
         $this->num_users = 0;
      }
   }
   
   public function created_timesince()
   {
      return $this->var2timesince($this->created);
   }

   public function url($full=FALSE)
   {
      if($full)
         return 'http://www.'.KE_DOMAIN.KE_PATH.'community/'.$this->name;
      else
         return KE_PATH.'community/'.$this->name;
   }
   
   public function set_name($name)
   {
      $name = strtolower($name); /// convertimos a minúsculas
      if( preg_match('/^[a-zA-Z0-9_]{3,20}$/i', $name) )
      {
         if( $this->db->select("SELECT * FROM ".$this->table_name." WHERE name = '".$name."';") )
         {
            $this->new_error_msg("Ya hay una comunidad con ese nombre, elige otro.");
            return FALSE;
         }
         else
         {
            $this->name = $name;
            return TRUE;
         }
      }
      else
      {
         $this->new_error_msg("Nombre de comunidad inválido. Sólo puede contener números, letras y _.
            Y debe tener una longitud de entre 3 y 20 caracteres.");
         return FALSE;
      }
   }
   
   public function set_description($desc)
   {
      $this->description = substr($this->nohtml($desc), 0, 200);
      return TRUE;
   }
   
   public function get($id)
   {
      if( isset($id) )
      {
         $c = $this->db->select("SELECT * FROM ".$this->table_name." WHERE id = '".$id."';");
         if($c)
            return new ke_community($c[0]);
         else
            return FALSE;
      }
      else
         return FALSE;
   }
   
   public function get_by_name($name)
   {
      if( isset($name) )
      {
         $c = $this->db->select("SELECT * FROM ".$this->table_name." WHERE name = '".$name."';");
         if($c)
            return new ke_community($c[0]);
         else
            return FALSE;
      }
      else
         return FALSE;
   }
   
   public function get_users()
   {
      $userlist = array();
      $user = new ke_user();
      $cu = new ke_community_user();
      $culist = $cu->all_from_community($this->id);
      if( $this->num_users != count($culist) )
      {
         $this->num_users = count($culist);
         $this->save();
      }
      foreach($culist as $cu2)
      {
         $user2 = $user->get($cu2->user_id);
         if($user2)
            $userlist[] = $user2;
      }
      return $userlist;
   }
   
   public function get_questions()
   {
      $qlist = array();
      $question = new ke_question();
      $cq = new ke_community_question();
      $cqlist = $cq->all_from_community($this->id);
      foreach($cqlist as $cq2)
      {
         $question2 = $question->get($cq2->question_id);
         if($question2)
            $qlist[] = $question2;
      }
      return $qlist;
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
         $sql = "UPDATE ".$this->table_name." SET name = ".$this->var2str($this->name).",
            description = ".$this->var2str($this->description).", created = ".$this->var2str($this->created).",
            num_users = ".$this->var2str($this->num_users)." WHERE id = '".$this->id."';";
      }
      else
      {
         $sql = "INSERT INTO ".$this->table_name." (name,description,created,num_users) VALUES
            (".$this->var2str($this->name).",".$this->var2str($this->description).",".$this->var2str($this->created).",
            ".$this->var2str($this->num_users).");";
      }
      return $this->db->exec($sql);
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM ".$this->table_name." WHERE id = '".$this->id."';");
   }
   
   public function all()
   {
      $clist = array();
      $communities = $this->db->select("SELECT * FROM ".$this->table_name." ORDER BY name ASC;");
      if($communities)
      {
         foreach($communities as $c)
            $clist[] = new ke_community($c);
      }
      return $clist;
   }
   
   public function add_user($uid)
   {
      $cu = new ke_community_user();
      $cu->user_id = $uid;
      $cu->community_id = $this->id;
      if( $cu->save() )
      {
         $this->num_users += 1;
         if( $this->save() )
            return TRUE;
         else
            return FALSE;
      }
      else
         return FALSE;
   }
   
   public function rm_user($uid)
   {
      $cu = new ke_community_user();
      $cu->user_id = $uid;
      $cu->community_id = $this->id;
      if( $cu->delete() )
      {
         $this->num_users -= 1;
         if( $this->num_users < 0 )
            $this->num_users = 0;
         
         if( $this->save() )
            return TRUE;
         else
            return FALSE;
      }
      else
         return FALSE;
   }
   
   public function user_is_member($uid)
   {
      $cu = new ke_community_user();
      $cu->user_id = $uid;
      $cu->community_id = $this->id;
      return $cu->exists();
   }

   public function add_question($qid)
   {
      $cq = new ke_community_question();
      $cq->question_id = $qid;
      $cq->community_id = $this->id;
      return $cq->save();
   }
   
   public function rm_question($qid)
   {
      $cq = new ke_community_question();
      $cq->question_id = $qid;
      $cq->community_id = $this->id;
      return $cq->delete();
   }
   
   public function question_is_here($qid)
   {
      $cq = new ke_community_question();
      $cq->question_id = $qid;
      $cq->community_id = $this->id;
      return $cq->exists();
   }
   
   public function avg_users()
   {
      $num = 0;
      $aux = $this->db->select("SELECT AVG(num_users) as num FROM ".$this->table_name.";");
      if($aux)
         $num = floatval($aux[0]['num']);
      return $num;
   }
}

?>
