<?php 

//Importar Conexión
use Model\ActiveRecord;
require 'includes/app.php';
// $db = conectarDB();
ActiveRecord::setDB($db);
//Crear Email y Password
$email = "mcidrobo@ladrilleramelendez.com.co";
$password = "C00rd1n4d0rS1st3mas";
// $password = "AuxS1st3m4sLM";
$passwordHash = password_hash($password, PASSWORD_BCRYPT);
//Query Para Crear Usuario 
$query = "INSERT INTO USUARIOS (EMAIL, PASSWORD) VALUES ('${email}', '${passwordHash}');";
echo $query;

 //Agregarlo A La Base De Datos
 mysqli_query($db, $query);
 ?>