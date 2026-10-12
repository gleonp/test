<?php
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['identificacion'])) {
    $identificacion = $_POST['identificacion'];

    // Consulta para verificar si el empleado existe y obtener el nombre del cuidador
    $sql = "SELECT * FROM empleados WHERE identificacion = '$identificacion'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $caregiver_name = $row['nombre']; // Ajusta el nombre del campo según tu estructura de base de datos

        // Obtener los IDs de los clientes asociados con el cuidador
        $sql_clients = "SELECT c.id, c.nombre FROM clientes c
                        INNER JOIN cuidador_cliente cc ON c.id = cc.cliente_id
                        WHERE cc.cuidador_id = {$row['id']}";
        $clients_result = $conn->query($sql_clients);

        $clients = array();
        while ($client_row = $clients_result->fetch_assoc()) {
            $clients[] = $client_row;
        }

        // Devolver el nombre del cuidador y los clientes en formato JSON
        echo json_encode(array(
            'caregiver_name' => $caregiver_name,
            'clients' => $clients
        ));
    } else {
        echo json_encode(['status' => 'no existe']);
        exit();
    }
}
?>
