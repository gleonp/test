<?php
include 'conexion.php';
session_start();

// Procesar el formulario de registro de tarea
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["registrarTarea"])) {
    // Recibir y escapar los datos del formulario (mejora la seguridad)
    $identificacion = mysqli_real_escape_string($conn, $_POST['identificacion']);
    $client_name = mysqli_real_escape_string($conn, $_POST['client_name']);
    $caregiver_name = mysqli_real_escape_string($conn, $_POST['caregiver_name']);
    $rol = mysqli_real_escape_string($conn, $_POST['rol']);
    $fecha_registro = mysqli_real_escape_string($conn, $_POST['fecha_registro']);
    $dia = date('l', strtotime($fecha_registro)); // Obtener el día de la semana
    $time_started = mysqli_real_escape_string($conn, $_POST['time_started']);
    $time_finished = mysqli_real_escape_string($conn, $_POST['time_finished']);
    $firma_empleado = mysqli_real_escape_string($conn, $_POST['firma_empleado']);
    $firma_cliente = mysqli_real_escape_string($conn, $_POST['firmaCliente']); // Recibir la firma del cliente en formato base64

    // Inicializar la variable para el nombre de archivo
    $file = '';

    // Convertir la firma base64 en una imagen y guardarla en el servidor si existe
    if ($firma_cliente) {
        // Eliminar los encabezados base64 (data:image/png;base64,)
        $firma_cliente = preg_replace('/^data:image\/\w+;base64,/', '', $firma_cliente);

        // Decodificar datos base64
        $firma_cliente_decoded = base64_decode($firma_cliente);

        // Verificar si la decodificación fue exitosa
        if ($firma_cliente_decoded !== false) {
            // Generar nombre de archivo único
            $file = '../images/firmas/' . uniqid() . '.png'; // Nombre de archivo con extensión PNG (puedes ajustarlo según el tipo de imagen)

            // Guardar la imagen en el servidor
            if (file_put_contents($file, $firma_cliente_decoded) === false) {
                // Error al guardar la imagen
                echo '<div class="container"><div class="alert alert-danger mt-3" role="alert">Error al guardar la firma en el servidor.</div></div>';
                echo '<div class="container"><a href="../" class="btn btn-primary mt-3">Back</a></div>';
                exit; // Detener la ejecución si hay un error
            }
        } else {
            // Error al decodificar la firma base64
            echo '<div class="container"><div class="alert alert-danger mt-3" role="alert">Error al decodificar la firma base64.</div></div>';
            echo '<div class="container"><a href="../" class="btn btn-primary mt-3">Back</a></div>';
            exit; // Detener la ejecución si hay un error
        }
    }    

    // Calcular total de horas
    $hora_inicio = new DateTime($time_started);
    $hora_fin = new DateTime($time_finished);
    $intervalo = $hora_inicio->diff($hora_fin);
    $total_hours = $intervalo->h + ($intervalo->i / 60);

    // Obtener el ID del empleado según la identificación
    $query = "SELECT id, nombre FROM empleados WHERE identificacion = '$identificacion'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $empleado_id = $row['id'];
        $nombre_empleado = $row['nombre'];

        // Insertar los datos en la tabla empleados_tareas
        $sql = "INSERT INTO empleados_tareas (empleado_id, client_name, caregiver_name, rol, fecha_registro, dia, time_started, time_finished, total_hours, firma_empleado, firma_cliente) 
                VALUES ('$empleado_id', '$client_name', '$caregiver_name', '$rol', '$fecha_registro', '$dia', '$time_started', '$time_finished', '$total_hours', '$firma_empleado', '$file')";

        if ($conn->query($sql) === TRUE) {
            $empleado_tarea_id = $conn->insert_id;

            // Insertar las tareas seleccionadas en la tabla tareas_seleccionadas
            if (isset($_POST['tareas']) && is_array($_POST['tareas'])) {
                foreach ($_POST['tareas'] as $tarea) {
                    $tarea = mysqli_real_escape_string($conn, $tarea);
                    $sql_tarea = "INSERT INTO tareas_seleccionadas (empleado_tarea_id, tarea) VALUES ('$empleado_tarea_id', '$tarea')";
                    $conn->query($sql_tarea);
                }
            }




            // --- Inicio: Guardar en registros_diarios ---
            // Procesar comidas, actividades y medicamentos (convertir en JSON)
            $comidas = json_encode(array_map(null, $_POST['comida'], $_POST['cantidad_comida'], $_POST['hora_comida'], $_POST['comentario_comida']));
            $actividades = json_encode(array_map(null, $_POST['actividad'], $_POST['duracion_actividad'], $_POST['hora_actividad'], $_POST['comentario_actividad']));
            $medicamentos = json_encode(array_map(null, $_POST['medicamento'], $_POST['dosis_medicamento'], $_POST['hora_medicamento'], $_POST['comentario_medicamento']));

            // Recuperar calificaciones y notas adicionales
            $dolor = $_POST['dolor'];
            $energia = $_POST['energia'];
            $sueno = $_POST['sueno'];
            $nauseas = $_POST['nauseas'];
            $notas_adicionales = mysqli_real_escape_string($conn, $_POST['notas_adicionales']);

            // Insertar en la tabla registros_diarios
            $sql_registros_diarios = "INSERT INTO registros_diarios (empleado_id, client_name, fecha, hora_inicio, hora_fin, comidas, actividades, medicamentos, dolor, energia, sueno, nauseas, notas_adicionales)
                    VALUES ('$empleado_id', '$client_name', '$fecha_registro', '$time_started', '$time_finished', '$comidas', '$actividades', '$medicamentos', '$dolor', '$energia', '$sueno', '$nauseas', '$notas_adicionales')";

            if ($conn->query($sql_registros_diarios) !== TRUE) {
                echo '<div class="container"><div class="alert alert-danger mt-3" role="alert">Error al guardar registros diarios: ' . $conn->error . '</div></div>';
                echo '<div class="container"><a href="../" class="btn btn-primary mt-3">Back</a></div>';
            }
            // --- Fin: Guardar en registros_diarios ---






            // Preparar los detalles del correo electrónico
            $to = 'contactus@aphomeproviderservice.com'; // Reemplazar con la dirección de correo destino
            $subject = 'New caregiver registration';
            $message = "The caregiver $nombre_empleado with ID $identificacion has created a record on $fecha_registro at $time_started.";
            $headers = 'From: no-reply@aphomeproviderservice.com' . "\r\n" .
                       'Reply-To: no-reply@aphomeproviderservice.com' . "\r\n" .
                       'X-Mailer: PHP/' . phpversion();

            // Enviar el correo electrónico
            $mail_success = mail($to, $subject, $message, $headers);

            // Mostrar mensajes de confirmación con estilo
            $alert_class = $mail_success ? 'alert-success' : 'alert-warning';
            $alert_message = $mail_success ? 'Your registration was successfully sent and saved.' : 'Your registration was successfully saved but the email could not be sent.';
            echo '<div class="container"><div class="alert ' . $alert_class . ' mt-3" role="alert">' . $alert_message . '</div></div>';
            echo '<div class="container"><a href="../" class="btn btn-primary mt-3">Back</a></div>';
        } else {
            echo '<div class="container"><div class="alert alert-danger mt-3" role="alert">Error: ' . $sql . '<br>' . $conn->error . '</div></div>';
            echo '<div class="container"><a href="../" class="btn btn-primary mt-3">Back</a></div>';
        }
    } else {
        echo '<div class="container"><div class="alert alert-danger mt-3" role="alert">Caregiver not found.</div></div>';
        echo '<a href="../" class="btn btn-primary mt-3">Back</a>';
    }
}

// Cerrar conexión
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/index.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <!-- Aquí puedes agregar el contenido del formulario o cualquier otra cosa -->
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>