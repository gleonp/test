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

// Procesar la creación de un nuevo cliente
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['crearCliente'])) {
    $nombre = $_POST['nombre'];

    // Insertar nuevo cliente
    $sql_insert = "INSERT INTO clientes (nombre) VALUES ('$nombre')";
    
    if ($conn->query($sql_insert) === TRUE) {
        $mensaje = "Client created successfully.";
        $tipoMensaje = 'success';
    } else {
        $mensaje = "Error to create new client: " . $conn->error;
        $tipoMensaje = 'danger';
    }
}

// Procesar la eliminación de un cliente
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminarCliente'])) {
    $cliente_id = $_POST['cliente_id'];

    // Eliminar cliente
    $sql_delete = "DELETE FROM clientes WHERE id = $cliente_id";
    
    if ($conn->query($sql_delete) === TRUE) {
        $mensaje = "Client deleted successfully.";
        $tipoMensaje = 'success';
    } else {
        $mensaje = "Error to delete client: " . $conn->error;
        $tipoMensaje = 'danger';
    }
}

// Obtener la lista de clientes
$sql_clientes = "SELECT * FROM clientes ORDER BY nombre ASC";
$result_clientes = $conn->query($sql_clientes);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Clients</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>    
    
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.js"></script>
    <link href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.css" rel="stylesheet">

    <link href="css/index.min.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Rochester&display=swap" rel="stylesheet">

</head>
<body>
<div class="container">


    <div class="admin-bar">
        <h2>Admin panel</h2>
        <div>            
            <a href="admin.php">Back to panel</a>
        </div>  
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-<?php echo $tipoMensaje; ?>" role="alert">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>




    <div class="card">
        <div class="card-header">Create client</div>
        <div class="card-body">
            <form method="post" action="manage_clients.php" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="nombre" class="form-label">Client name:</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
                <div class="invalid-feedback">
                    Please enter cliente name.
                </div>
            </div>
            <button type="submit" class="btn btn-primary" name="crearCliente">Create client</button>
            </form>
        </div>
    </div>

    <br />
    <br />
    
    <?php if ($result_clientes->num_rows > 0): ?>
        <table class="table table-striped table-bordered" id="listClients">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <!--<th>Date</th>-->
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_clientes->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['nombre']; ?></td>
                        <!--<td><?php echo $row['fecha_registro']; ?></td>-->
                        <td class="align-right">
                            <form method="post" action="manage_clients.php" class="d-inline">
                                <input type="hidden" name="cliente_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="eliminarCliente" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>There are no records.</p>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js"></script>
<script>

    new DataTable('#listClients');

    (function () {
        'use strict';
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
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
