<?php
include 'conexion.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

// Procesar la solicitud AJAX
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identificacion = mysqli_real_escape_string($conn, $_POST['identificacion']);
    $filtroSemana = mysqli_real_escape_string($conn, $_POST['filtroSemana']);
    $fechaInicio = mysqli_real_escape_string($conn, $_POST['fechaInicio']);
    $fechaFin = mysqli_real_escape_string($conn, $_POST['fechaFin']);
    $clienteSeleccionado = mysqli_real_escape_string($conn, $_POST['cliente']);

    // Obtener el ID del empleado según la identificación
    $query = "SELECT id, nombre FROM empleados WHERE identificacion = '$identificacion'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $empleado_id = $row['id'];
        $nombre_cuidador = $row['nombre'];

        // Calcular el rango de fechas según el filtro seleccionado
        if ($filtroSemana == 'actual') {
            $fechaInicio = date('Y-m-d', strtotime('monday this week'));
            $fechaFin = date('Y-m-d', strtotime('sunday this week'));
        }

        // Consultar las tareas realizadas en el rango de fechas y para el cliente seleccionado
        $sql_tareas = "SELECT et.id, et.client_name, et.caregiver_name, et.rol, et.fecha_registro, et.firma_cliente, et.firma_empleado, ts.tarea 
                    FROM empleados_tareas et
                    JOIN tareas_seleccionadas ts ON et.id = ts.empleado_tarea_id
                    WHERE et.empleado_id = '$empleado_id' 
                    AND et.client_name = '$clienteSeleccionado' 
                    AND et.fecha_registro BETWEEN '$fechaInicio' AND '$fechaFin'
                    ORDER BY et.fecha_registro";        

        $result_tareas = $conn->query($sql_tareas);
        

        // Consultar las horas trabajadas en el rango de fechas y para el cliente seleccionado
        $sql_horas = "SELECT id, fecha_registro, time_started, time_finished, total_hours
                    FROM empleados_tareas
                    WHERE empleado_id = '$empleado_id' 
                    AND client_name = '$clienteSeleccionado'
                    AND fecha_registro BETWEEN '$fechaInicio' AND '$fechaFin'
                    ORDER BY fecha_registro";


        $result_horas = $conn->query($sql_horas);

        if (($result_tareas && $result_tareas->num_rows > 0) || ($result_horas && $result_horas->num_rows > 0)) {
            // Construir la tabla de tareas
            if ($result_tareas && $result_tareas->num_rows > 0) {
                $tareas = [];
                while ($row = $result_tareas->fetch_assoc()) {
                    $tareas[$row['tarea']][$row['fecha_registro']] = true;
                    $firmaCliente = $row['firma_cliente'];
                    $firmaCuidador = $row['firma_empleado'];
                    $nombreCliente = $row['client_name'];
                    $nombreCaregiver = $row['caregiver_name'];
                }
                
                echo '<br /><br /><br />';
                echo '<table class="table table-bordered">';
                echo '<tr>';
                echo '<td><h4>Caregiver: '. $nombreCaregiver .'</h4></td>';
                echo '<td><h4>Client: '. $nombreCliente .'</h4></td>';
                echo '</td>';
                echo '</tr>';
                echo '<br />';
                echo '<div class="table-responsive"><table class="table table-bordered">';
                echo '<thead><tr><th>--</th><th>Monday</th><th>Tuesday</th><th>Wednesday</th><th>Thursday</th><th>Friday</th><th>Saturday</th><th>Sunday</th></tr></thead>';
                echo '<tbody>';

                $dias_semana = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

                foreach ($tareas as $tarea => $dias) {
                    $tarea_sin_guiones = str_replace('_', ' ', $tarea); // Reemplaza los guiones bajos por espacios
                    echo '<tr>';
                    echo '<td class="tarea">' . $tarea_sin_guiones . '</td>';
                    foreach ($dias_semana as $dia) {
                        $fecha_dia = date('Y-m-d', strtotime($dia . ' this week', strtotime($fechaInicio)));
                        if (isset($dias[$fecha_dia])) {
                            echo '<td class="center check">✔</td>';
                        } else {
                            echo '<td class="center cross">✘</td>';
                        }                        
                    }                    
                    echo '</tr>';
                }

                echo '</tbody>';
                echo '</table>';
            }

            // Construir la tabla de horas trabajadas
            if ($result_horas && $result_horas->num_rows > 0) {
                $horas = [];
                $total_horas = 0;
                
                while ($row = $result_horas->fetch_assoc()) {

                    $fecha = $row['fecha_registro'];
                    $time_started = $row['time_started'];
                    $time_finished = $row['time_finished'];
                    $id = $row['id'];

                    // Convertir a timestamps
                    $timestamp_started = strtotime($time_started);
                    $timestamp_finished = strtotime($time_finished);
                    $rounded_started = round($timestamp_started / 900) * 900;
                    $rounded_finished = round($timestamp_finished / 900) * 900;                    
                    $total_hours_rounded = ($rounded_finished - $rounded_started) / 3600;
                    
                    $horas[$fecha][] = [
                        'time_started' => $time_started,
                        'time_finished' => $time_finished,
                        'id' => $id,
                        'total_hours' => round($total_hours_rounded, 2)                        
                    ];

                    $horas_actuales = floatval($row['total_hours']);
                    $total_horas_semana += $total_hours_rounded;

                }
                
                echo '<table class="table table-bordered table-horas">';
                echo '<thead><tr><th>Day</th><th>Date</th><th>Time started</th><th>Time finished</th><th>Total hours</th></tr></thead>';
                echo '<tbody>';

                foreach ($dias_semana as $dia) {
                    $fecha_dia = date('Y-m-d', strtotime($dia . ' this week', strtotime($fechaInicio)));
                    echo '<tr>';
                    echo '<td>' . ucfirst($dia) . '</td>';
                    echo '<td>' . $fecha_dia . '</td>';
            
                    if (isset($horas[$fecha_dia])) {
                        // Mostrar todos los horarios para el día
                        echo '<td>';
                        foreach ($horas[$fecha_dia] as $registro) {
                            echo $registro['time_started'] . '<br />';
                        }
                        echo '</td>';
                        echo '<td>';
                        foreach ($horas[$fecha_dia] as $registro) {
                            echo $registro['time_finished'] . '<br />';
                        }
                        echo '</td>';
                        echo '<td><div class="btn-eliminar">';
                        foreach ($horas[$fecha_dia] as $registro) {
                            echo $registro['total_hours'] . '<button class="btn-sm eliminar-registro" data-id="' . $registro['id'] . '">x</button><br />';
                        }                        
                        echo '</div></td>';
                    } else {
                        echo '<td>✘</td><td>✘</td><td>✘</td>';
                    }
            
                    echo '</tr>';
                }

                // Añadir fila para el total de horas
                echo '<tr>';
                echo '<td colspan="4"><strong>Total hours of the week</strong></td>';
                echo '<td><strong>' . round($total_horas_semana, 2) . '</strong></td>';
                echo '</tr>';

                echo '</tbody>';
                echo '</table><br />';

                echo '<table class="table tabla-firmas">';
                echo '<tr>';
                echo '<td width="50%">Signed by Client: <span><img style="max-height: 5rem; width: auto;" src="' . $firmaCliente . '"></span></td>';
                echo '<td width="50%">Signed by Caregiver: <span>' . $firmaCuidador . '</span></td>';
                echo '</tr>';
                echo '</table></div>';
                
            }
        } else {
            echo "<div class='alert alert-warning' role='alert'>No tasks or hours worked were found for the caregiver and client in the selected date range.</div>";
        }
    } else {
        echo "<div class='alert alert-danger' role='alert'>Caregiver not found.</div>";
    }
}

// Cerrar conexión
$conn->close();
?>
