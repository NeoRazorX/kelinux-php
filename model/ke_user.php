<?php

require_once 'core/ke_model.php';
require_once 'model/ke_question.php';
require_once 'model/ke_notification.php';
require_once 'model/ke_community.php';

class ke_user extends ke_model
{
   public $id;
   public $email;
   public $password;
   public $nick;
   public $log_key;
   public $points;
   public $created;
   public $last_log_in;
   public $no_emails;
   public $num_unreaded; /// para las notificaciones
   
   public function __construct($u=FALSE)
   {
      parent::__construct('users');
      if($u)
      {
         $this->id = $this->intval($u['id']);
         $this->email = $u['email'];
         $this->password = $u['password'];
         $this->nick = $u['nick'];
         $this->log_key = $u['log_key'];
         $this->points = $this->intval($u['points']);
         $this->created = $u['created'];
         $this->last_log_in = $u['last_log_in'];
         $this->no_emails = ($u['no_emails'] == 't');
      }
      else
      {
         $this->id = NULL;
         $this->email = '';
         $this->password = '';
         $this->nick = '';
         $this->log_key = NULL;
         $this->points = 10;
         $this->created = Date('j-n-Y H:i:s');
         $this->last_log_in = Date('j-n-Y H:i:s');
         $this->no_emails = FALSE;
      }
   }
   
   public function created_timesince()
   {
      return $this->var2timesince($this->created);
   }
   
   public function last_log_in_timesince()
   {
      return $this->var2timesince($this->last_log_in);
   }

   public function url()
   {
      return KE_PATH.'/user/'.$this->nick;
   }
   
   public function get_questions($offset=0, $limit=KE_ITEM_LIMIT)
   {
      $question = new ke_question();
      return $question->all_from_user($this->id, $offset, $limit);
   }
   
   public function get_notifications()
   {
      $n = new ke_notification();
      return $n->all_from_user($this->id);
   }
   
   public function num_unreaded_notifications()
   {
      if( !isset($this->num_unreaded) )
      {
         $n = new ke_notification();
         $this->num_unreaded = $n->num_unreaded_from_user($this->id);
      }
      return $this->num_unreaded;
   }
   
   public function mark_all_notifications_readed()
   {
      $n = new ke_notification();
      $n->mark_as_readed_from_user($this->id);
   }

   public function get_communities()
   {
      $commlist = array();
      $community = new ke_community();
      $cu = new ke_community_user();
      foreach($cu->all_from_user($this->id) as $cu2)
      {
         $community2 = $community->get($cu2->community_id);
         if($community2)
            $commlist[] = $community2;
      }
      return $commlist;
   }
   
   public function set_nick($n)
   {
      if( preg_match('/^[a-zA-Z0-9_]{3,16}$/i', $n) )
      {
         if( $this->db->select("SELECT * FROM ".$this->table_name." WHERE nick = '".$n."';") )
         {
            $this->new_error_msg("El nick elegido ya está asignado a alguien, elige otro.");
            return FALSE;
         }
         else
         {
            $this->nick = $n;
            return TRUE;
         }
      }
      else
      {
         $this->new_error_msg("Nick no válido. Debe tener entre 3 y 16 caracteres
            y estar formado por letras, números o _.");
         return FALSE;
      }
   }
   
   public function set_email($e)
   {
      if( filter_var($e, FILTER_VALIDATE_EMAIL) )
      {
         if( $this->db->select("SELECT * FROM ".$this->table_name." WHERE email = '".$e."';") )
         {
            $this->new_error_msg("Ya hay una cuenta asociada al email ".$e);
            return FALSE;
         }
         else
         {
            $this->email = $e;
            return TRUE;
         }
      }
      else
      {
         $this->new_error_msg("Email no válido.");
         return FALSE;
      }
   }
   
   public function set_password($p)
   {
      if( preg_match('/^[a-zA-Z0-9_]{3,10}$/i', $p) )
      {
         $this->password = sha1($p);
         return TRUE;
      }
      else
      {
         $this->new_error_msg("Contraseña no válida. Debe tener entre 3 y 10 caracteres
            formados por letras, números o _.");
         return FALSE;
      }
   }
   
   public function new_log_key()
   {
      $this->log_key = sha1( strval(rand()) );
      $this->last_log_in = Date('j-n-Y H:i:s');
   }
   
   public function is_admin()
   {
      return ($this->nick == KE_ADMIN_NICK);
   }
   
   public function add_points($p=1)
   {
      $this->points += intval($p);
      if($this->points < 1)
      {
         if( $this->is_admin() )
            $this->points = 1;
         else
            $this->points = 0;
      }
      else if($this->points > 500)
         $this->points = 500;
      return $this->save();
   }
   
   public function get($id)
   {
      if( isset($id) )
      {
         $u = $this->db->select("SELECT * FROM ".$this->table_name." WHERE id = '".$id."';");
         if($u)
            return new ke_user($u[0]);
         else
            return FALSE;
      }
      else
         return FALSE;
   }
   
   public function get_by_nick($nick)
   {
      if( isset($nick) )
      {
         $u = $this->db->select("SELECT * FROM ".$this->table_name." WHERE nick = '".$nick."';");
         if($u)
            return new ke_user($u[0]);
         else
            return FALSE;
      }
      else
         return FALSE;
   }
   
   public function get_by_email($email)
   {
      if( isset($email) )
      {
         $u = $this->db->select("SELECT * FROM ".$this->table_name." WHERE email = '".$email."';");
         if($u)
            return new ke_user($u[0]);
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
         $sql = "UPDATE ".$this->table_name." SET email = ".$this->var2str($this->email).",
            password = ".$this->var2str($this->password).", nick = ".$this->var2str($this->nick).",
            log_key = ".$this->var2str($this->log_key).", points = ".$this->var2str($this->points).",
            created = ".$this->var2str($this->created).", last_log_in = ".$this->var2str($this->last_log_in).",
            no_emails = ".$this->var2str($this->no_emails)." WHERE id = '".$this->id."';";
         return $this->db->exec($sql);
      }
      else
      {
         $sql = "INSERT INTO ".$this->table_name." (email,password,nick,log_key,points,created,last_log_in,no_emails)
            VALUES (".$this->var2str($this->email).",".$this->var2str($this->password).",".$this->var2str($this->nick).",
            ".$this->var2str($this->log_key).",".$this->var2str($this->points).",".$this->var2str($this->created).",
            ".$this->var2str($this->last_log_in).",".$this->var2str($this->no_emails).");";
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
      return $this->db->exec("DELETE FROM ".$this->table_name." WHERE id = '".$this->id."';");
   }
   
   public function all()
   {
      $ulist = array();
      $users = $this->db->select("SELECT * FROM ".$this->table_name." ORDER BY nick ASC;");
      if($users)
      {
         foreach($users as $u)
            $ulist[] = new ke_user($u);
      }
      return $ulist;
   }
   
   public function last_logged($limit=KE_ITEM_LIMIT)
   {
      $ulist = array();
      $users = $this->db->select_limit("SELECT * FROM ".$this->table_name." ORDER BY last_log_in DESC", 0, $limit);
      if($users)
      {
         foreach($users as $u)
            $ulist[] = new ke_user($u);
      }
      return $ulist;
   }
   
   public function new_users($limit=KE_ITEM_LIMIT)
   {
      $ulist = array();
      $users = $this->db->select_limit("SELECT * FROM ".$this->table_name." ORDER BY created DESC", 0, $limit);
      if($users)
      {
         foreach($users as $u)
            $ulist[] = new ke_user($u);
      }
      return $ulist;
   }
   
   public function top_users($limit=KE_ITEM_LIMIT)
   {
      $ulist = array();
      $users = $this->db->select_limit("SELECT * FROM ".$this->table_name." ORDER BY points DESC", 0, $limit);
      if($users)
      {
         foreach($users as $u)
            $ulist[] = new ke_user($u);
      }
      return $ulist;
   }
   
   public function avg_points()
   {
      $points = 0;
      $aux = $this->db->select("SELECT AVG(points) as points FROM ".$this->table_name.";");
      if($aux)
         $points = floatval($aux[0]['points']);
      return $points;
   }
}

?>
