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
    $clienteSeleccionado = mysqli_real_escape_string($conn, $_POST['cliente']);

    $query = "SELECT id, nombre FROM empleados WHERE identificacion = '$identificacion'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row_empleado = $result->fetch_assoc();
        $empleado_id = $row_empleado['id'];
        $nombre_empleado = $row_empleado['nombre'];

        $sql_actividades = "SELECT fecha, hora_inicio, hora_fin, comidas, actividades, medicamentos, dolor, energia, sueno, nauseas, notas_adicionales, client_name
                            FROM registros_diarios
                            WHERE empleado_id = '$empleado_id' 
                            AND client_name = '$clienteSeleccionado'
                            AND fecha BETWEEN '$fechaInicio' AND '$fechaFin'
                            ORDER BY fecha";

        $result_actividades = $conn->query($sql_actividades);

        if ($result_actividades && $result_actividades->num_rows > 0) {
            echo '<br /><br /><br />';
            echo '<table class="table table-bordered">';
            echo '<thead><tr><th colspan="6"></th></tr></thead><tbody>';  // Encabezado genérico

            while ($row = $result_actividades->fetch_assoc()) {
                echo '<tr><td colspan="6">';
                echo '<strong>Caregiver:</strong> ' . htmlspecialchars($nombre_empleado) . ' | <strong>Client:</strong> ' . htmlspecialchars($row['client_name']) .'| <strong>Date:</strong> ' . $row['fecha'] . ' | <strong>Start Time:</strong> ' . $row['hora_inicio'] . ' | <strong>End Time:</strong> ' . $row['hora_fin'] . 
                      '</td></tr>';
                
                echo '<tr><td colspan="6">';
                echo '<div class="row">';
                echo '<div class="col-md-4">' . formatNestedTable($row['comidas'], ['Food', 'Amount', 'Time', 'Comment']) . '</div>';
                echo '<div class="col-md-4">' . formatNestedTable($row['actividades'], ['Activity', 'Duration', 'Time', 'Comment']) . '</div>';
                echo '<div class="col-md-4">' . formatNestedTable($row['medicamentos'], ['Medication', 'Dose', 'Time', 'Comment']) . '</div>';
                echo '</div></td></tr>';

                echo '<tr><td colspan="6" class="ratings"><strong>Pain:</strong> ' . $row['dolor'] . ' | <strong>Energy:</strong> ' . $row['energia'] . ' | <strong>Sleep:</strong> ' . $row['sueno'] . ' | <strong>Nausea:</strong> ' . $row['nauseas'] . '</td></tr>';
                echo '<tr><td colspan="6" class="notes"><strong>Notes:</strong> ' . $row['notas_adicionales'] . '</td></tr>';
            }

            echo '</tbody></table>';
        } else {
            echo "<div class='alert alert-warning' role='alert'>No activities found for the caregiver and client in the selected date range.</div>";
        }
    } else {
        echo "<div class='alert alert-danger' role='alert'>Caregiver not found.</div>";
    }
}

$conn->close();

function formatNestedTable($json, $headers) {
    $data = json_decode($json, true);
    if ($data && is_array($data)) {
        $output = '<table class="table table-bordered table-sm">';
        $output .= '<thead><tr>';
        foreach ($headers as $header) {
            $output .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $output .= '</tr></thead><tbody>';
        foreach ($data as $row) {
            $output .= '<tr>';
            foreach ($row as $cell) {
                $output .= '<td>' . htmlspecialchars($cell) . '</td>';
            }
            $output .= '</tr>';
        }
        $output .= '</tbody></table>';
        return $output;
    } else {
        return 'N/A';
    }
}
?>