<?php

require_once 'ke_db.php';
require_once 'ke_tools.php';

abstract class ke_model extends ke_tools
{
   protected $db;
   protected $table_name;
   public $errors;
   
   public function __construct($name)
   {
      $this->db = new ke_db();
      $this->table_name = $name;
      $this->errors = '';
   }
   
   protected function new_error_msg($msg)
   {
      $this->errors .= $msg;
   }

   /*
    * Esta función devuelve TRUE si los datos del objeto se encuentran
    * en la base de datos.
    */
   abstract public function exists();

   /*
    * Esta función sirve tanto para insertar como para actualizar
    * los datos del objeto en la base de datos.
    */
   abstract public function save();
   
   /// Esta función sirve para eliminar los datos del objeto de la base de datos
   abstract public function delete();
   
   /// devuelve el número de elementos de la tabla
   public function total()
   {
      $num = 0;
      $aux = $this->db->select("SELECT COUNT(*) as num FROM ".$this->table_name.";");
      if($aux)
         $num = intval($aux[0]['num']);
      return $num;
   }
}

?>
