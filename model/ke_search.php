<?php

require_once 'core/ke_cache.php';
require_once 'model/ke_question.php';

class ke_search_line
{
   public $query;
   public $times;
   
   public function __construct($q='')
   {
      $this->query = $q;
      $this->times = 1;
   }
}

class ke_search extends ke_cache
{
   private $history;
   public $query;
   
   public function __construct()
   {
      parent::__construct();
      $this->history = $this->get_array('search_history');
      if( isset($_POST['query']) )
         $this->query = $_POST['query'];
      else
         $this->query = '';
   }
   
   public function save()
   {
      $this->set('search_history', $this->history);
   }
   
   public function clean()
   {
      unset($this->history);
      $this->history = array();
      $this->delete('search_history');
   }

   public function new_search()
   {
      if($this->query != '')
      {
         $question = new ke_question();
         $encontrada = FALSE;
         foreach($this->history as $h)
         {
            if($h->query == $this->query)
            {
               $h->times++;
               $encontrada = TRUE;
               break;
            }
         }
         if( !$encontrada )
            $this->history[] = new ke_search_line($this->query);
         $this->save();
         return $question->search($this->query);
      }
      else
         return array();
   }
   
   public function get_history()
   {
      $resultado = array();
      while( count($resultado) < count($this->history) )
      {
         $seleccionado = NULL;
         foreach($this->history as $h)
         {
            if( !in_array($h, $resultado) )
            {
               if( is_null($seleccionado) )
                  $seleccionado = $h;
               else if( $h->times > $seleccionado->times )
                  $seleccionado = $h;
            }
         }
         $resultado[] = $seleccionado;
      }
      return $resultado;
   }
   
   public function total()
   {
      $num = 0;
      foreach($this->history as $h)
         $num += $h->times;
      return $num;
   }
   
   public function get_tags($text='')
   {
      $tags = array();
      foreach($this->history as $h)
      {
         if( preg_match('/'.$h->query.'($|\z|\W)/i', $text) AND !in_array($h, $tags))
            $tags[] = $h->query;
      }
      $other_tags = split(', ', KE_TAGS);
      foreach($other_tags as $t)
      {
         if( preg_match('/'.$t.'($|\z|\W)/i', $text) AND !in_array($t, $tags))
            $tags[] = $t;
      }
      return $tags;
   }
}

?>
