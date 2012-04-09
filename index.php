<?php

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
