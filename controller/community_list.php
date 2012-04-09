<?php

class community_list extends ke_controller
{
   public function __construct()
   {
      parent::__construct('community_list', 'Comunidades');
   }
   
   protected function process()
   {
      if( isset($_POST['cname']) AND isset($_POST['cdesc']) )
      {
         if( $this->community->set_name($_POST['cname']) )
         {
            $this->community->set_description($_POST['cdesc']);
            if( $this->community->save() )
               header('location: '.$this->community->url());
            else
               $this->new_error("¡Imposible crear la comunidad! ".$this->community->errors);
         }
         else
            $this->new_error("¡Imposible crear la comunidad! ".$this->community->errors);
      }
   }
}

?>
