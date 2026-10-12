<?php
include 'app/conexion.php';
session_start();

// Verificar si el administrador está autenticado
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

$mensaje = '';
$tipoMensaje = '';

// Obtener todos los clientes para mostrar en el select
$sql_clientes = "SELECT id, nombre FROM clientes";
$result_clientes = $conn->query($sql_clientes);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['crearEmpleado'])) {
    $nombre = $_POST['nombre'];
    $identificacion = $_POST['identificacion'];
    $clientes = $_POST['clientes'];

    // Verificar si el empleado ya existe
    $sql = "SELECT * FROM empleados WHERE identificacion = '$identificacion'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $mensaje = "Error: The caregiver with this ID already exists.";
        $tipoMensaje = 'danger';
    } else {
        // Insertar nuevo empleado
        $sql_insert = "INSERT INTO empleados (nombre, identificacion) VALUES ('$nombre', '$identificacion')";
        
        if ($conn->query($sql_insert) === TRUE) {
            $empleado_id = $conn->insert_id;
            
            // Insertar en la tabla cuidador_cliente
            foreach ($clientes as $cliente_id) {
                $sql_asociar = "INSERT INTO cuidador_cliente (cuidador_id, cliente_id) VALUES ('$empleado_id', '$cliente_id')";
                $conn->query($sql_asociar);
            }

            $mensaje = "Successfully created caregiver and assigned clients.";
            $tipoMensaje = 'success';
        } else {
            $mensaje = "Error creating caregiver: " . $conn->error;
            $tipoMensaje = 'danger';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/index.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <div class="admin-bar">
        <h2>Create caregiver</h2>
        <a href="admin.php">Back to panel</a>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-<?php echo $tipoMensaje; ?>" role="alert">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="create_caregiver.php" class="needs-validation" novalidate>
        <div class="row">
            <div class="mb-3 col-6">
                <label for="nombre" class="form-label">Name:</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
                <div class="invalid-feedback">
                    Please enter the caregiver's name.
                </div>
            </div>
            <div class="mb-3 col-6">
                <label for="identificacion" class="form-label">ID:</label>
                <input type="text" class="form-control" id="identificacion" name="identificacion" required>
                <div class="invalid-feedback">
                    Please enter the caregiver's ID.
                </div>
            </div>
        </div>
        <div class="mb-3">
        <label for="clientes" class="form-label">Assign Clients:</label>
        <select class="form-select" id="clientes" name="clientes[]" multiple required>
            <?php
            $sql = "SELECT id, nombre FROM clientes";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<option value="' . $row['id'] . '">' . $row['nombre'] . '</option>';
                }
            }
            ?>
        </select>
        <div class="invalid-feedback">
            Please select at least one client.
        </div>
    </div>
        <button type="submit" class="btn btn-primary" name="crearEmpleado">Create</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js"></script>
<script>
    (function () {
        'use strict';
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
</script>
</body>
</html>
