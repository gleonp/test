<?php
include 'app/conexion.php';
session_start();

if (!isset($_SESSION['admin'])) {
    echo 'unauthorized';
    exit();
}

$id = $_POST['id'];
$identificacion = $_POST['identificacion'];
$nombre = $_POST['nombre'];
$clientes = $_POST['clientes']; // Array de IDs de clientes

// Actualizar los datos del cuidador
$query = "UPDATE empleados SET identificacion = ?, nombre = ? WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssi", $identificacion, $nombre, $id);

if ($stmt->execute()) {
    // Eliminar asociaciones existentes
    $query = "DELETE FROM cuidador_cliente WHERE cuidador_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Insertar nuevas asociaciones
    $query = "INSERT INTO cuidador_cliente (cuidador_id, cliente_id) VALUES (?, ?)";
    $stmt = $conn->prepare($query);

    foreach ($clientes as $client_id) {
        $stmt->bind_param("ii", $id, $client_id);
        $stmt->execute();
    }

    echo 'success';
} else {
    echo 'error';
}

$stmt->close();
$conn->close();
?>
