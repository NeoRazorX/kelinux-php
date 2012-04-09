<?php

require_once 'core/ke_model.php';

class ke_notification extends ke_model
{
   public $id;
   public $user_id;
   public $date;
   public $text;
   public $link;
   public $sendmail;
   public $readed;
   
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
         $this->sendmail = ($n['sendmail'] == 't');
         $this->readed = ($n['readed'] == 't');
      }
      else
      {
         $this->id = NULL;
         $this->user_id = NULL;
         $this->date = Date('j-n-Y H:i:s');
         $this->text = '';
         $this->link = '';
         $this->sendmail = FALSE;
         $this->readed = FALSE;
      }
   }
   
   public function timesince()
   {
      return $this->var2timesince($this->date);
   }
   
   public function set_text($t)
   {
      $this->text = $this->nohtml($t);
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
         {
            $nlist[] = new ke_notification($n);
         }
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
   
   public function mark_as_readed_from_user($uid)
   {
      return $this->db->exec("UPDATE ".$this->table_name." SET readed = TRUE WHERE user_id = '".$uid."' AND readed = FALSE;");
   }
}

?>
