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

class ke_cache
{
   private static $cache;
   private static $connected;
   private static $num_subclases;

   public function __construct()
   {
      if( !isset(self::$cache) )
      {
         self::$cache = new Memcache();
         try
         {
            self::$connected = self::$cache->connect(KE_CACHE_HOST, KE_CACHE_PORT);
         }
         catch (Exception $e)
         {
            self::$connected = FALSE;
         }
         self::$num_subclases = 0;
      }
      self::$num_subclases += 1;
   }
   
   public function __destruct()
   {
      if( self::$connected )
      {
         self::$num_subclases -= 1;
         if( self::$num_subclases < 1 )
            self::$cache->close();
      }
   }
   
   public function set($key, $object)
   {
      if( self::$connected )
         self::$cache->set($key, $object, FALSE, 604800);
   }
   
   public function get($key)
   {
      if( self::$connected )
         return self::$cache->get($key);
      else
         return FALSE;
   }
   
   public function get_array($key)
   {
      $aa = array();
      if( self::$connected )
      {
         $a = self::$cache->get($key);
         if($a)
            $aa = $a;
      }
      return $aa;
   }

   public function delete($key)
   {
      if( self::$connected )
         self::$cache->delete($key);
   }
}

?>
