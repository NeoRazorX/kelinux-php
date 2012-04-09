<?php

class community extends ke_controller
{
   public $scommunity;
   
   public function __construct()
   {
      parent::__construct('community', 'Comunidad: ');
   }
   
   protected function process()
   {
      if( isset($_GET['param1']) )
      {
         $this->scommunity = $this->community->get_by_name($_GET['param1']);
         if($this->scommunity)
         {
            $this->title .= $this->scommunity->name;
            if( isset($_GET['param2']) )
            {
               if($_GET['param2'] == 'join')
               {
                  $this->scommunity->add_user( $this->user->id );
               }
               else if($_GET['param2'] == 'leave')
               {
                  $this->scommunity->rm_user( $this->user->id );
               }
            }
         }
         else
            $this->new_error("¡Comunidad no encontrada!");
      }
      else
      {
         $this->scommunity = FALSE;
         $this->new_error("¡Comunidad no encontrada!");
      }
   }
   
   public function url()
   {
      if($this->scommunity)
         return $this->scommunity->url();
      else
         return KE_PATH.'/community_list';
   }
   
   public function get_description()
   {
      if($this->scommunity)
         return $this->scommunity->description;
      else
         return $this->title;
   }
}

?>
