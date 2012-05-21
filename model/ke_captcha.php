<?php

class ke_captcha_item
{
   public $key;
   public $pregunta;
   public $respuestas;
   
   public function __construct($k, $p, $r1, $r2=FALSE, $r3=FALSE)
   {
      $this->key = $k;
      $this->pregunta = $p;
      $this->respuestas = array($r1);
      if($r2)
         $this->respuestas[] = $r2;
      if($r3)
         $this->respuestas[] = $r3;
   }
}

class ke_captcha
{
   private $data;
   
   public function __construct()
   {
      $this->data = array();
      $this->data[] = new ke_captcha_item('a', '¿Cuanto es uno más uno?', 'dos', '2');
      $this->data[] = new ke_captcha_item('b', '¿Cuanto es dos más dos?', 'cuatro', '4');
      $this->data[] = new ke_captcha_item('c', '¿Cuanto es tres más tres?', 'seis', '6');
      $this->data[] = new ke_captcha_item('d', '¿Cuanto es cuatro más cuatro?', 'ocho', '8');
   }
   
   public function get_html()
   {
      $item = $this->data[ array_rand($this->data) ];
      return "<div class='captcha'><b>Captcha:</b> ".$item->pregunta."<input type='hidden' name='captcha_key' value='".$item->key."'/>
         <input type='text' name='captcha_solution' size='6'/></div>";
   }
   
   public function solved()
   {
      $resultado = FALSE;
      if( isset($_POST['captcha_key']) AND isset($_POST['captcha_solution']))
      {
         foreach($this->data as $item)
         {
            if($item->key == $_POST['captcha_key'] AND in_array(strtolower($_POST['captcha_solution']), $item->respuestas))
               $resultado = TRUE;
         }
      }
      return $resultado;
   }
}

?>
