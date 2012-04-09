<?php

class chat_room extends ke_controller
{
   public $full_html;
   
   public function __construct()
   {
      parent::__construct('chat_room', 'Chat');
   }
   
   protected function process()
   {
      $this->full_html = TRUE;
      $this->chat->all_comments_readed();
      
      if( isset($_POST['comment']) )
      {
         $this->chat->new_comment($this->user, $_POST['comment']);
         if($this->user)
            $this->user->add_points(1);
      }
      else if( isset($_POST['reload']) )
      {
         $this->full_html = FALSE;
      }
   }
}

?>
