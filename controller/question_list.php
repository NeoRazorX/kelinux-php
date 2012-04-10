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

require_once 'model/ke_question.php';

class question_list extends ke_controller
{
   public $question;
   public $resultado;
   public $offset;
   
   public function __construct()
   {
      parent::__construct('question_list', 'Preguntas');
   }
   
   protected function process()
   {
      $this->question = new ke_question();
      
      if( isset($_GET['param2']) )
         $this->offset = intval($_GET['param2']);
      else
         $this->offset = 0;
      
      $this->resultado = $this->question->all($this->offset);
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
