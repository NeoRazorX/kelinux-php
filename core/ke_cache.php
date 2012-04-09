<?php

class ke_cache
{
   private $cache;
   private $connected;

   public function __construct()
   {
      $this->cache = new Memcache();
      try
      {
         $this->cache->connect(KE_CACHE_HOST, KE_CACHE_PORT);
         $this->connected = TRUE;
      }
      catch (Exception $e)
      {
         $this->connected = FALSE;
      }
   }
   
   public function __destruct()
   {
      $this->cache->close();
   }
   
   public function set($key, $object)
   {
      if($this->connected)
         $this->cache->set($key, $object, FALSE, 86400);
   }
   
   public function get($key)
   {
      if($this->connected)
         return $this->cache->get($key);
      else
         return FALSE;
   }
   
   public function get_array($key)
   {
      $aa = array();
      if($this->connected)
      {
         $a = $this->cache->get($key);
         if($a)
            $aa = $a;
      }
      return $aa;
   }

   public function delete($key)
   {
      if($this->connected)
         $this->cache->delete($key);
   }
}

?>
