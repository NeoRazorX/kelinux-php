<?php
/*
 * This file is part of Kelinux-php.
 * Copyright (C) 2012  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

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
      foreach($this->community->all() as $c)
      {
         $fecha = explode(' ', $c->created);
         echo '<url><loc>',$c->url(TRUE),'</loc><lastmod>',$fecha[0],'</lastmod><changefreq>always</changefreq><priority>0.7</priority></url>';
      }
      foreach($this->question->all(0, 500) as $q)
      {
         $fecha = explode(' ', $q->updated);
         echo '<url><loc>',$q->url(TRUE),'</loc><lastmod>',$fecha[0],'</lastmod><changefreq>always</changefreq><priority>0.8</priority></url>';
      }
      echo '</urlset>';
      
      /// añadimos una entrada al log
      $this->log->new_line('SITEMAP consultado');
   }
}

?>
