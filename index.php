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

require_once 'config.php';
require_once 'core/ke_controller.php';
require_once 'raintpl/rain.tpl.class.php';

/// ¿Qué controlador usar?
if( isset($_GET['page']) )
{
   if( file_exists('controller/'.$_GET['page'].'.php') )
   {
      require_once 'controller/'.$_GET['page'].'.php';
      $kec = new $_GET['page']();
   }
   else
      $kec = new ke_controller();
}
else
{
   require_once 'controller/main_page.php';
   $kec = new main_page();
}

if( $kec->template )
{
   /// configuramos rain.tpl
   raintpl::configure("base_url", NULL);
   raintpl::configure("path_replace", FALSE);
   raintpl::configure("tpl_dir", "view/");
   raintpl::configure("cache_dir", "tmp/");
   $tpl = new RainTPL();
   $tpl->assign('kec', $kec);
   $tpl->draw( $kec->template );
}

?>
