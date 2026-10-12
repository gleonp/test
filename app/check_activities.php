<?php
include 'conexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identificacion = mysqli_real_escape_string($conn, $_POST['identificacion']);
    $fechaInicio = mysqli_real_escape_string($conn, $_POST['fechaInicio']);
    $fechaFin = mysqli_real_escape_string($conn, $_POST['fechaFin']);
    $clienteSeleccionado = mysqli_real_escape_string($conn, $_POST['cliente']);

    $query = "SELECT id FROM empleados WHERE identificacion = '$identificacion'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $empleado_id = $row['id'];

        $sql_check = "SELECT 1 FROM registros_diarios 
                      WHERE empleado_id = '$empleado_id' 
                      AND client_name = '$clienteSeleccionado' 
                      AND fecha BETWEEN '$fechaInicio' AND '$fechaFin' LIMIT 1";

        $result_check = $conn->query($sql_check);

        if ($result_check && $result_check->num_rows > 0) {
            echo 'exists';
        } else {
            echo 'not_exists';
        }
    }
}

$conn->close();
?>