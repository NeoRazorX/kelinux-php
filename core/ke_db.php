<?php

class ke_db
{
   private static $link;
   private static $t_selects;
   private static $t_transactions;
   private static $history;
   
   public function __construct()
   {
      if(!self::$link)
      {
         self::$t_selects = 0;
         self::$t_transactions = 0;
         self::$history = array();
      }
   }
   
   /// devuelve el número de selects ejecutados
   public function get_selects()
   {
      return self::$t_selects;
   }
   
   /// devuele le número de transacciones realizadas
   public function get_transactions()
   {
      return self::$t_transactions;
   }
   
   public function get_history()
   {
      return self::$history;
   }

   /// conecta con la base de datos
   public function connect()
   {
      $connected = FALSE;
      if(!self::$link)
      {
         self::$link = mysql_pconnect(KE_MYSQL_HOST, KE_MYSQL_USER, KE_MYSQL_PASS);
         if(self::$link)
         {
            mysql_select_db(KE_MYSQL_DB);
            $connected = TRUE;
         }
      }
      return $connected;
   }
   
   /// desconecta de la base de datos
   public function close()
   {
      $retorno = FALSE;
      if(self::$link)
      {
         $retorno = mysql_close(self::$link);
         self::$link = NULL;
      }
      return $retorno;
   }
   
   /// ejecuta un select
   public function select($sql)
   {
      $resultado = FALSE;
      if(self::$link)
      {
         self::$history[] = $sql;
         $filas = mysql_query($sql, self::$link);
         if($filas)
         {
            $resultado = array();
            while ($row = mysql_fetch_array($filas))
               $resultado[] = $row;
            mysql_free_result($filas);
         }
         self::$t_selects++;
      }
      return($resultado);
   }
   
   public function select_limit($sql, $offset=0, $limit=KE_ITEM_LIMIT)
   {
      $resultado = FALSE;
      if(self::$link)
      {
         self::$history[] = $sql;
         $filas = mysql_query($sql.' LIMIT '.$limit.' OFFSET '.$offset, self::$link);
         if($filas)
         {
            $resultado = array();
            while ($row = mysql_fetch_array($filas))
               $resultado[] = $row;
            mysql_free_result($filas);
         }
         self::$t_selects++;
      }
      return($resultado);
   }
   
   /// ejecuta una consulta sobre la base de datos
   public function exec($sql)
   {
      $resultado = FALSE;
      if(self::$link)
      {
         self::$history[] = $sql;
         $resultado = mysql_query($sql, self::$link);
         self::$t_transactions++;
      }
      return($resultado);
   }
}

?>
