<?php
include 'app/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = $_POST['id'];
    
    $sql = "SELECT cliente_id FROM cuidador_cliente WHERE cuidador_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    $assigned_clients = [];
    while ($row = $result->fetch_assoc()) {
        $assigned_clients[] = $row['cliente_id'];
    }
    
    echo json_encode(['assigned_clients' => $assigned_clients]);
    $stmt->close();
}

$conn->close();
?>