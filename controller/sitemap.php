<?php

class sitemap extends ke_controller
{
   private $question;
   
   public function __construct()
   {
      parent::__construct('sitemap', 'sitemap');
   }
   
   protected function process()
   {
      $this->template = FALSE; /// desactivamos el motor de plantillas
      $this->question = new ke_question();
      
      header("Content-type: text/xml");
      echo '<?xml version="1.0" encoding="UTF-8"?>';
      echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
      foreach($this->question->all() as $q)
         echo '<url><loc>',$q->url(),'</loc><lastmod>',$q->created,'</lastmod><changefreq>always</changefreq><priority>0.9</priority></url>';
      echo '</urlset>';
   }
}

?>
