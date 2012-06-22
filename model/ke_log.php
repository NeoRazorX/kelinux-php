<?php

require_once 'core/ke_cache.php';
require_once 'core/ke_tools.php';

class ke_log_line extends ke_tools
{
   public $date;
   public $ip;
   public $browser;
   public $url;
   public $info;
   
   public function __construct($txt='')
   {
      $this->date = Date('d-m-Y H:i:s');
      $this->ip = $_SERVER['REMOTE_ADDR'];
      try {
         $this->browser = $_SERVER['HTTP_USER_AGENT'];
      }
      catch (Exception $e) {
         $this->browser = 'UNKNOWN';
      }
      try {
         $this->url = $_SERVER['REQUEST_URI'];
      }
      catch (Exception $e) {
         $this->url = 'UNKNOWN';
      }
      $this->info = $txt;
   }
   
   public function timesince()
   {
      return $this->var2timesince($this->date);
   }
}

class ke_log_stat
{
   public $url;
   public $clics;
   
   public function __construct($u)
   {
      $this->url = $u;
      $this->clics = 1;
   }
   
   static public function cmp_obj($a, $b)
   {
      if($a->clics > $b->clics)
         return -1;
      else if($a->clics == $b->clics)
         return 0;
      else
         return 1;
   }
}

class ke_log extends ke_cache
{
   private $history;
   private $stats;
   
   public function __construct()
   {
      $this->history = $this->get_array('log_history');
      $this->stats = $this->get_array('stats');
      $this->url2stats();
   }
   
   public function clean()
   {
      unset($this->history);
      $this->history = array();
      $this->delete('log_history');
      
      unset($this->stats);
      $this->stats = array();
      $this->delete('stats');
   }
   
   public function new_line($txt='')
   {
      $this->history[] = new ke_log_line($txt);
      $this->set('log_history', $this->history);
   }
   
   private function url2stats()
   {
      if(substr($_SERVER['REQUEST_URI'], strlen(KE_PATH), 9) == 'question/')
      {
         $encontrada = FALSE;
         foreach($this->stats as &$s)
         {
            if($s->url == $_SERVER['REQUEST_URI'])
            {
               $s->clics++;
               $encontrada = TRUE;
            }
         }
         if( !$encontrada )
            $this->stats[] = new ke_log_stat($_SERVER['REQUEST_URI']);
         $this->set('stats', $this->stats);
      }
   }

   public function get_history()
   {
      return array_reverse($this->history);
   }
   
   public function get_stats()
   {
      usort($this->stats, array('ke_log_stat', 'cmp_obj'));
      return $this->stats;
   }
}

?>
