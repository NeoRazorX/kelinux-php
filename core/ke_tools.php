<?php

class ke_tools
{
   /// función auxiliar para facilitar la generación de SQL
   public function var2str($v)
   {
      if( is_null($v) )
         return 'NULL';
      else if( is_bool($v) )
      {
         if($v)
            return 'TRUE';
         else
            return 'FALSE';
      }
      else if( count(explode('-', $v)) == 3 ) /// es una fecha
         return "'".Date('Y-m-d H:i:s', strtotime($v))."'";
      else
         return "'".$v."'";
   }
   
   public function intval($s)
   {
      if( is_null($s) )
         return NULL;
      else
         return intval($s);
   }

   /// functión auxiliar para facilitar el uso de fechas
   public function var2timesince($v)
   {
      if( isset($v) )
      {
         $v = strtotime($v);
         $time = time() - $v;
         
         if($time <= 60)
            return 'hace '.round($time/60,0).' segundos';
         else if(60 < $time && $time <= 3600)
            return 'hace '.round($time/60,0).' minutos';
         else if(3600 < $time && $time <= 86400)
            return 'hace '.round($time/3600,0).' horas';
         else if(86400 < $time && $time <= 604800)
            return 'hace '.round($time/86400,0).' dias';
         else if(604800 < $time && $time <= 2592000)
            return 'hace '.round($time/604800,0).' semanas';
         else if(2592000 < $time && $time <= 29030400)
            return 'hace '.round($time/2592000,0).' meses';
         else if($time > 29030400)
            return 'hace más de un año';
      }
      else
         return 'fecha desconocida';
   }
   
   /// función para facilitar la generación del códigos html
   public function var2html($v)
   {
      $a = array(
          "/\[i\](.*?)\[\/i\]/is",
          "/\[b\](.*?)\[\/b\]/is",
          "/\[u\](.*?)\[\/u\]/is",
          "/\[big\](.*?)\[\/big\]/is",
          "/\[small\](.*?)\[\/small\]/is",
          "/\[code\](.*?)\[\/code\]/is",
          "/\[img\](.*?)\[\/img\]/is",
          "/\[url\](.*?)\[\/url\]/is",
          "/\[youtube\](.*?)\[\/youtube\]/is"
      );
      $b = array(
          "<i>$1</i>",
          "<b>$1</b>",
          "<u>$1</u>",
          "<big>$1</big>",
          "<small>$1</small>",
          "<code>$1</code>",
          "<img src=\"$1\" />",
          "<a href=\"$1\" target=\"_Blank\">$1</a>",
          "<div><iframe width=\"420\" height=\"345\" src=\"http://www.youtube.com/embed/$1\"".
             "frameborder=\"0\" allowfullscreen></iframe></div>"
      );
      $texto = nl2br( preg_replace($a, $b, $v) );
      return $texto;
   }
   
   /* 
    * Dado un texto devuelve ese mismo texto sin rastro de html.
    * Uso esta función en lugar de htmlspecialchars para poder añadir más restricciones
    * o cambios cuando quiera.
    */
   public function nohtml($t)
   {
      return htmlspecialchars($t);
   }
}

?>
