<?php
$servername = "localhost";
$username = "opbcvcpp_control";
$password = ")^HftELCtKzH";
$dbname = "opbcvcpp_control";

//$servername = "localhost";
//$username = "root";
//$password = "";
//$dbname = "control";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Configurar el conjunto de caracteres a utf8mb4
$conn->set_charset("utf8mb4");

?>