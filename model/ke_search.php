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
   
   static public function cmp_obj($a, $b)
   {
      if($a->times > $b->times)
         return -1;
      else if($a->times == $b->times)
         return 0;
      else
         return 1;
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
         /// guardamos la búsqueda
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
         
         $question = new ke_question();
         $results = $question->search($this->query, 0, 100);
         if(count($results) > 0)
            return $this->custom_question_sort($results);
         else
         {
            $results = array();
            $tags = preg_split('/ /', $this->query);
            foreach($tags as $q)
            {
               foreach($question->search($q, 0, 100) as $re2)
               {
                  if( !in_array($re2, $results) )
                     $results[] = $re2;
               }
            }
            return $this->custom_question_sort($results, $tags);
         }
      }
      else
         return array();
   }
   
   private function custom_question_sort($questions, $tags=FALSE)
   {
      if( !$tags )
         $tags = array($this->query);
      
      $cambios = 0;
      $i = 0;
      while($i < count($questions))
      {
         $pi = 0;
         foreach($tags as $t)
         {
            /// posición del tag $t en el texto de la pregunta $i
            $pos = stripos($questions[$i]->text, $t);
            if($pos !== FALSE)
               $pi += $pos;
            else
               $pi += 1000;
         }
         
         /// array(mejor j, valor pj del mejor j)
         $cambio = array(-1, 1000000);
         
         $j = 1 + $i;
         while($j < count($questions))
         {
            $pj = 0;
            foreach($tags as $t)
            {
               /// posición del tag $t en el texto de la pregunta $j
               $pos = stripos($questions[$j]->text, $t);
               if($pos !== FALSE)
                  $pj += $pos;
               else
                  $pj += 1000;
            }
            if($pj < $pi AND $pj < $cambio[1])
            {
               $cambio[0] = $j;
               $cambio[1] = $pj;
            }
            $j++;
         }
         if($cambio[0] > -1)
         {
            $aux = $questions[$i];
            $questions[$i] = $questions[$cambio[0]];
            $questions[$cambio[0]] = $aux;
            $cambios++;
         }
         $i++;
      }
      return $questions;
   }
   
   public function get_history()
   {
      usort($this->history, array('ke_search_line', 'cmp_obj'));
      return $this->history;
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
