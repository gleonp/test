<?php

include 'conexion.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

if (isset($_POST['id'])) {
    
    $id = $_POST['id'];

    // Primero eliminar los registros relacionados en tareas_seleccionadas
    $sql_delete_related = "DELETE FROM tareas_seleccionadas WHERE empleado_tarea_id = ?";
    $stmt_related = $conn->prepare($sql_delete_related);
    
    if ($stmt_related === false) {
        die("Error en la preparación de la consulta (tareas_seleccionadas): " . $conn->error);
    }
    
    $stmt_related->bind_param('i', $id);
    $result_related = $stmt_related->execute();

    if ($result_related) {
        // Después eliminar el registro en empleados_tareas
        $sql = "DELETE FROM empleados_tareas WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            die("Error en la preparación de la consulta: " . $conn->error);
        }
        
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();

        if ($result) {
            echo "Record deleted successfully.";
        } else {
            echo "Error al eliminar el registro: " . $stmt->error;
        }
    } else {
        echo "Error al eliminar los registros relacionados: " . $stmt_related->error;
    }
} else {
    echo "ID no especificado.";
}

// Cerrar la conexión
$stmt_related->close();
$stmt->close();
$conn->close();
?>