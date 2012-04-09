<?php

require_once 'core/ke_cache.php';
require_once 'core/ke_tools.php';

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
      $this->history[] = new ke_chat_line($user, $text);
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
