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
require_once 'model/ke_answer.php';
require_once 'model/ke_community.php';
require_once 'model/ke_notification.php';

class ke_question extends ke_model
{
   public $id;
   public $text;
   public $user_id;
   public $created;
   public $updated;
   public $num_answers;
   public $status;
   public $reward;
   public $user;
   public $communities;

   public function __construct($q=FALSE)
   {
      parent::__construct('questions');
      if($q)
      {
         $this->id = $this->intval($q['id']);
         $this->text = $q['text'];
         $this->user_id = $this->intval($q['user_id']);
         $this->created = $q['created'];
         $this->updated = $q['updated'];
         $this->num_answers = intval($q['num_answers']);
         
         $this->status = intval($q['status']);
         if($this->status == 0 AND $this->num_answers > 0)
            $this->status = 1;
         
         $this->reward = intval($q['reward']);
         if($this->is_solved() AND $this->reward != 0)
         {
            $this->reward = 0;
            $this->save();
         }
         
         $this->user = new ke_user();
         $this->user = $this->user->get($this->user_id);
      }
      else
      {
         $this->id = NULL;
         $this->text = '';
         $this->user_id = NULL;
         $this->created = Date('j-n-Y H:i:s');
         $this->updated = Date('j-n-Y H:i:s');
         $this->num_answers = 0;
         $this->status = 0;
         $this->reward = 1;
         $this->user = FALSE;
      }
   }
   
   public function title()
   {
      $aux = $this->nobbcode($this->text);
      if(strlen($aux) > 80)
         return substr($aux, 0, 80)."...";
      else
         return $aux;
   }
   
   public function resume()
   {
      $aux = $this->nobbcode($this->text);
      if(strlen($aux) > 300)
         return substr($aux, 0, 300)."...";
      else
         return $aux;
   }
   
   public function text2html()
   {
      return $this->var2html($this->text);
   }
   
   public function created_timesince()
   {
      return $this->var2timesince($this->created);
   }
   
   public function updated_timesince()
   {
      return $this->var2timesince($this->updated);
   }
   
   public function set_text($t)
   {
      $this->text = $this->nohtml($t);
   }

   public function get_status($s=NULL)
   {
      if( !isset($s) )
         $s = $this->status;
      if($s == 0)
         return 'nueva';
      else if($s == 1)
         return 'abierta';
      else if($s == 2)
         return 'incompleta';
      else if($s == 9)
         return 'parcialmente solucionada';
      else if($s == 10)
         return "pendiente de confirmación";
      else if($s == 11)
         return 'solucionada';
      else if($s == 20)
         return 'duplicada';
      else if($s == 21)
         return 'erronea';
      else if($s == 22)
         return 'antigua';
      else
         return 'estado desconocido';
   }
   
   public function get_status_array()
   {
      $statuslist = array();
      for($i=0; $i<30; $i++)
      {
         if( $this->get_status($i) != 'estado desconocido' )
            $statuslist[] = array(
                'num' => $i,
                'text' => $this->get_status($i)
            );
      }
      return $statuslist;
   }
   
   public function set_solved()
   {
      $this->updated = Date('j-n-Y H:i:s');
      $this->reward = 0;
      $this->status = 11;
      return $this->save();
   }

   public function is_solved()
   {
      return ($this->status >= 10);
   }
   
   public function num_solved()
   {
      $num = 0;
      $aux = $this->db->select("SELECT COUNT(*) as num FROM ".$this->table_name." WHERE status >= 10;");
      if($aux)
         $num = intval($aux[0]['num']);
      return $num;
   }
   
   public function status_stats()
   {
      $stats = array(
          0 => 0,
          1 => 0,
          2 => 0,
          3 => 0,
          4 => 0,
          5 => 0,
          6 => 0,
          7 => 0,
          8 => 0,
          9 => 0,
          10 => 0,
          11 => 0,
          12 => 0,
          13 => 0,
          14 => 0,
          15 => 0,
          16 => 0,
          17 => 0,
          18 => 0,
          19 => 0,
          20 => 0,
          21 => 0,
          22 => 0
      );
      $aux = $this->db->select("SELECT status, COUNT(*) as num FROM ".$this->table_name." GROUP BY status;");
      if($aux)
      {
         foreach($aux as $s)
         {
            if( intval($s['num']) > 0)
               $stats[ intval($s['status']) ] = intval($s['num']);
         }
      }
      return $stats;
   }
   
   public function url($full=FALSE)
   {
      if($full)
         return 'http://www.'.KE_DOMAIN.KE_PATH."question/".$this->id;
      else
         return KE_PATH."question/".$this->id;
   }
   
   public function is_readed()
   {
      if( isset($_COOKIE['q_'.$this->id]) )
         return ( strtotime($_COOKIE['q_'.$this->id]) >= strtotime($this->updated) );
      else
         return FALSE;
   }
   
   public function mark_as_readed()
   {
      setcookie('q_'.$this->id, Date('Y-m-d H:i:s'), time()+2592000, KE_PATH); /// expira en 30 dias
      /// una de cada 5 veces añadimos un punto a la recompensa
      if( !$this->is_solved() AND rand(0, 4) == 0 )
         $this->add_reward();
   }
   
   public function add_reward($p=1)
   {
      if( !$this->is_solved() )
      {
         $this->reward += intval($p);
         return $this->save();
      }
      else
         return FALSE;
   }
   
   public function get_answers()
   {
      $answer = new ke_answer();
      $answers = $answer->all_from_question($this->id);
      if( count($answers) != $this->num_answers)
      {
         /// si han añadido respuestas hay que actualizar y generar notificaciones
         if( count($answers) > $this->num_answers)
         {
            $this->updated = Date('j-n-Y H:i:s');
            if($this->num_answers > 0 AND $this->status == 0)
               $this->status = 1;
            
            $i = $this->num_answers;
            while($i < count($answers))
            {
               /* ¿Generamos notificaciones por mención directa a usuarios?
                * Nos guardamos la lista de usuarios mencionados para no enviarles
                * más de una notificación por respuesta.
                */
               $users_mentioned = $answers[$i]->get_users_mentioned($this->user);
               foreach($users_mentioned as $u)
               {
                  $noti = new ke_notification();
                  $noti->user_id = $u->id;
                  $noti->link = $answers[$i]->url();
                  $noti->set_mention($answers[$i]->user, $answers[$i]->text);
                  $noti->save();
               }
               
               /* Añadimos al creador de la pregunta para no enviarle más de una notificación
                * por respuesta.
                */
               $users_mentioned[] = $this->user;
               
               /// ¿generamos notificaiones por mención a otra respuesta? ejemplo: '@2 gracias!'
               foreach($answers[$i]->get_numbers_mentioned() as $n)
               {
                  if($answers[$n-1]->user AND !in_array($answers[$n-1]->user, $users_mentioned))
                  {
                     $noti = new ke_notification();
                     $noti->user_id = $answers[$n-1]->user_id;
                     $noti->link = $answers[$i]->url();
                     $noti->set_mention($answers[$i]->user, $answers[$i]->text);
                     $noti->save();
                     
                     $users_mentioned[] = $answers[$n-1]->user;
                  }
               }
               
               /// ¿generamos una notificación para el autor de la pregunta?
               if($this->user AND $this->user_id != $answers[$i]->user_id)
               {
                  $noti = new ke_notification();
                  $noti->user_id = $this->user_id;
                  $noti->link = $answers[$i]->url();
                  $noti->set_answer($answers[$i]->user, $answers[$i]->text);
                  $noti->save();
               }
               
               $i++;
            }
         }
         
         $this->num_answers = count($answers);
         $this->save();
      }
      return $answers;
   }
   
   public function get_communities()
   {
      if( !isset($this->communities) )
      {
         $this->communities = array();
         $community = new ke_community();
         $cq = new ke_community_question();
         foreach($cq->all_from_question($this->id) as $cq2)
         {
            $comm2 = $community->get($cq2->community_id);
            if($comm2)
               $this->communities[] = $comm2;
         }
      }
      return $this->communities;
   }
   
   public function search($query='')
   {
      $qlist = array();
      if($query != '')
      {
         $questions = $this->db->select_limit("SELECT DISTINCT * FROM ".$this->table_name." WHERE text LIKE '%".$query."%'
            OR id IN (SELECT question_id FROM answers WHERE text LIKE '%".$query."%') ORDER BY updated DESC");
         if($questions)
         {
            foreach($questions as $q)
               $qlist[] = new ke_question($q);
         }
      }
      return $qlist;
   }
   
   public function get($id)
   {
      if( isset($id) )
      {
         $q = $this->db->select("SELECT * FROM ".$this->table_name." WHERE id = '".$id."';");
         if($q)
            return new ke_question($q[0]);
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
            user_id = ".$this->var2str($this->user_id).", created = ".$this->var2str($this->created).",
            updated = ".$this->var2str($this->updated).", num_answers = ".$this->var2str($this->num_answers).",
            status = ".$this->var2str($this->status).", reward = ".$this->var2str($this->reward)."
            WHERE id = '".$this->id."';";
         return $this->db->exec($sql);
      }
      else
      {
         $sql = "INSERT INTO ".$this->table_name." (text,user_id,created,updated,num_answers,status,reward) VALUES
            (".$this->var2str($this->text).",".$this->var2str($this->user_id).",".$this->var2str($this->created).",
            ".$this->var2str($this->updated).",".$this->var2str($this->num_answers).",".$this->var2str($this->status).",
            ".$this->var2str($this->reward).");";
         if( $this->db->exec($sql) )
         {
            $id = $this->db->select("SELECT LAST_INSERT_ID() as id;");
            if($id)
            {
               $this->id = intval($id[0]['id']);
               return TRUE;
            }
            else
               return FALSE;
         }
         else
            return FALSE;
      }
   }
   
   public function delete()
   {
      /// eliminamos las relaciones con comunidades
      $cq = new ke_community_question();
      foreach($cq->all_from_question($this->id) as $cq2)
         $cq2->delete();
      /// eliminamos las respuestas
      $answer = new ke_answer();
      foreach($answer->all_from_question($this->id) as $a)
         $a->delete();
      /// eliminamos la pregunta
      return $this->db->exec("DELETE FROM ".$this->table_name." WHERE id = '".$this->id."';");
   }
   
   public function all($offset=0, $limit=KE_ITEM_LIMIT, $order='updated')
   {
      $qlist = array();
      if($order == 'updated')
         $questions = $this->db->select_limit("SELECT * FROM ".$this->table_name." ORDER BY updated DESC", $offset, $limit);
      else if($order == 'created')
         $questions = $this->db->select_limit("SELECT * FROM ".$this->table_name." ORDER BY created DESC", $offset, $limit);
      else if($order == 'status')
         $questions = $this->db->select_limit("SELECT * FROM ".$this->table_name." ORDER BY status ASC", $offset, $limit);
      else if($order == 'reward')
         $questions = $this->db->select_limit("SELECT * FROM ".$this->table_name." ORDER BY reward DESC", $offset, $limit);
      else
         $questions = $this->db->select_limit("SELECT * FROM ".$this->table_name." ORDER BY updated DESC", $offset, $limit);
      if($questions)
      {
         foreach($questions as $q)
            $qlist[] = new ke_question($q);
      }
      return $qlist;
   }
   
   public function all_from_user($uid, $offset=0, $limit=KE_ITEM_LIMIT, $order='updated')
   {
      $qlist = array();
      if($order == 'updated')
         $questions = $this->db->select_limit("SELECT DISTINCT * FROM ".$this->table_name." WHERE user_id = '".$uid."'
            OR id IN (SELECT question_id FROM answers WHERE user_id = '".$uid."') ORDER BY updated DESC", $offset, $limit);
      else if($order == 'created')
         $questions = $this->db->select_limit("SELECT DISTINCT * FROM ".$this->table_name." WHERE user_id = '".$uid."'
            OR id IN (SELECT question_id FROM answers WHERE user_id = '".$uid."') ORDER BY created DESC", $offset, $limit);
      else if($order == 'status')
         $questions = $this->db->select_limit("SELECT DISTINCT * FROM ".$this->table_name." WHERE user_id = '".$uid."'
            OR id IN (SELECT question_id FROM answers WHERE user_id = '".$uid."') ORDER BY status ASC", $offset, $limit);
      else if($order == 'reward')
         $questions = $this->db->select_limit("SELECT DISTINCT * FROM ".$this->table_name." WHERE user_id = '".$uid."'
            OR id IN (SELECT question_id FROM answers WHERE user_id = '".$uid."') ORDER BY reward DESC", $offset, $limit);
      else if($order == 'author')
         $questions = $this->db->select_limit("SELECT DISTINCT * FROM ".$this->table_name." WHERE user_id = '".$uid."'
            OR id IN (SELECT question_id FROM answers WHERE user_id = '".$uid."') ORDER BY user_id DESC", $offset, $limit);
      else
         $questions = $this->db->select_limit("SELECT DISTINCT * FROM ".$this->table_name." WHERE user_id = '".$uid."'
            OR id IN (SELECT question_id FROM answers WHERE user_id = '".$uid."') ORDER BY updated DESC", $offset, $limit);
      if($questions)
      {
         foreach($questions as $q)
            $qlist[] = new ke_question($q);
      }
      return $qlist;
   }
   
   public function all_unsolved($offset=0, $limit=KE_ITEM_LIMIT)
   {
      $qlist = array();
      $questions = $this->db->select_limit("SELECT * FROM ".$this->table_name." WHERE status < 10 ORDER BY status ASC", $offset, $limit);
      if($questions)
      {
         foreach($questions as $q)
            $qlist[] = new ke_question($q);
      }
      return $qlist;
   }
   
   public function avg_reward()
   {
      $reward = 0;
      $aux = $this->db->select("SELECT AVG(reward) as reward FROM ".$this->table_name." WHERE status < 10;");
      if($aux)
         $reward = floatval($aux[0]['reward']);
      return $reward;
   }
   
   /// marcamos como antiguas las preguntas no solucionadas con más de 5 meses sin actualizaciones
   public function mark_old_questions()
   {
      $date = Date("Y-m-d", strtotime(Date("Y-m-d") . " -5 month"));
      return $this->db->exec("UPDATE ".$this->table_name." SET status = 22 WHERE status < 10 AND updated < '".$date."';");
   }
}

?>
