<?php

class user extends ke_controller
{
   public $suser;
   public $resultado;
   public $offset;
   
   public function __construct()
   {
      parent::__construct('user', 'Usuario: ');
   }
   
   protected function process()
   {
      if( isset($_GET['param1']) )
      {
         $this->suser = new ke_user();
         $this->suser = $this->suser->get_by_nick($_GET['param1']);
         if($this->suser)
         {
            $this->title .= $this->suser->nick;
            
            if( isset($_GET['param3']) )
               $this->offset = intval($_GET['param3']);
            else
               $this->offset = 0;
            
            $this->resultado = $this->suser->get_questions($this->offset);
         }
         else
            $this->new_error("¡Usuario no encontrado!");
      }
      else
      {
         $this->suser = FALSE;
         $this->new_error("¡Usuario no encontrado!");
      }
   }
   
   public function url()
   {
      if($this->suser)
         return $this->suser->url();
      else
         return KE_PATH.'user_list';
   }

   public function anterior_url()
   {
      $url = '';
      if($this->offset > 0)
         $url = $this->url()."/created/".($this->offset-KE_ITEM_LIMIT);
      return $url;
   }
   
   public function siguiente_url()
   {
      $url = '';
      if(count($this->resultado) == KE_ITEM_LIMIT)
         $url = $this->url()."/created/".($this->offset+KE_ITEM_LIMIT);
      return $url;
   }
}

?>
