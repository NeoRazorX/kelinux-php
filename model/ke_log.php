<?php

require_once 'core/ke_cache.php';
require_once 'core/ke_tools.php';

class ke_log_line
{
   public $date;
   public $ip;
   public $browser;
   public $url;
   public $info;
   
   public function __construct($txt='', $u='http://')
   {
      $this->date = Date('d-m-Y H:i:s');
      $this->ip = $_SERVER['REMOTE_ADDR'];
      try {
         $this->browser = $_SERVER['HTTP_USER_AGENT'];
      }
      catch (Exception $e) {
         $this->browser = 'UNKNOWN';
      }
      $this->url = $u;
      $this->info = $txt;
   }
}

class ke_log extends ke_cache
{
   private $history;
   
   public function __construct()
   {
      $this->history = $this->get_array('log_history');
   }
   
   public function clean()
   {
      unset($this->history);
      $this->history = array();
      $this->delete('log_history');
   }
   
   public function new_line($txt='', $u='http://')
   {
      $this->history[] = new ke_log_line($txt, $u);
      $this->set('log_history', $this->history);
   }
   
   public function get_history()
   {
      return array_reverse($this->history);
   }
}

?>
