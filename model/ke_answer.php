<?php

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
   
   public function url()
   {
      return KE_PATH.'/question/'.$this->question_id.'#'.$this->id;
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
