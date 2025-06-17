<?php
$DB_HOST = "localhost";
$DB_USER = "postgres";
$DB_PASS = "motor";
$DB_DB = "COOFFE_SHOP";

try{
    $conn = new PDO ("pgsql:host=$DB_HOST; dbname=$DB_DB; port=3305", $DB_USER, $DB_PASS);
}catch(exception $exp){
    die("Ha ocurrido un error en la conexion a BD ".$exp->getMessage());
}

?>