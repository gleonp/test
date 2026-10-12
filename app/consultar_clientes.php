<?php
include 'conexion.php';

session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identificacion = mysqli_real_escape_string($conn, $_POST['identificacion']);
    $fechaInicio = mysqli_real_escape_string($conn, $_POST['fechaInicio']);
    $fechaFin = mysqli_real_escape_string($conn, $_POST['fechaFin']);

    // Obtener el ID del empleado según la identificación
    $query = "SELECT id FROM empleados WHERE identificacion = '$identificacion'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $empleado_id = $row['id'];

        // Consultar los nombres de los clientes dentro del rango de fechas
        $sql_clientes = "SELECT DISTINCT client_name 
                         FROM empleados_tareas 
                         WHERE empleado_id = '$empleado_id' AND fecha_registro BETWEEN '$fechaInicio' AND '$fechaFin'
                         ORDER BY client_name";

        $result_clientes = $conn->query($sql_clientes);

        if ($result_clientes && $result_clientes->num_rows > 0) {
            while ($row = $result_clientes->fetch_assoc()) {
                echo "<option value='{$row['client_name']}'>{$row['client_name']}</option>";
            }
        } else {
            echo "<option value=''>No clients found</option>";
        }
    } else {
        echo "<option value=''>Caregiver not found</option>";
    }
    $conn->close();
}
?>
