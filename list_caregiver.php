<?php
include 'app/conexion.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

// Obtener la lista de empleados
$query = "SELECT id, identificacion, nombre FROM empleados";
$result = $conn->query($query);

// Obtener la lista de clientes
$query_clients = "SELECT id, nombre FROM clientes";
$clients_result = $conn->query($query_clients);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caregivers list</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.js"></script>
    <link href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.css" rel="stylesheet">

    <link href="css/index.min.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Rochester&display=swap" rel="stylesheet">
</head>
<body>
<div id="editModal" class="modal fade" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Caregiver</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" name="id" id="caregiverId">
                    <div class="mb-3">
                        <label for="identificacion" class="form-label">Identification</label>
                        <input type="text" class="form-control" id="identificacion" name="identificacion" required>
                    </div>
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Name</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="clientes" class="form-label">Assign Clients</label>
                        <select multiple class="form-select" id="clientes" name="clientes[]">
                            <?php while ($client = $clients_result->fetch_assoc()): ?>
                                <option value="<?php echo $client['id']; ?>"><?php echo $client['nombre']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveChanges">Save changes</button>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="admin-bar">
        <h2>Caregiver list</h2>
        <div>
            <a href="create_caregiver.php">Create caregiver</a>
            &nbsp;|&nbsp;
            <a href="admin.php">Back to panel</a>
        </div>            
    </div>
    
    <table class="table table-striped table-bordered" id="listCaregiver">
        <thead>
            <tr>
                <th class="align-left">Identification</th>
                <th class="align-left">Name</th>
                <th class="align-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="align-left"><?php echo $row['identificacion']; ?></td>
                        <td class="align-left"><?php echo $row['nombre']; ?></td>
                        <td class="align-right">
                            <a href="admin.php?idcaregiver=<?php echo $row['identificacion']; ?>" class="btn btn-primary">View records</a>
                            <button class="btn btn-secondary edit-btn" data-id="<?php echo $row['id']; ?>">Edit</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">Caregiver not found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    new DataTable('#listCaregiver');

    $(document).ready(function() {
        $('body').on('click', '.edit-btn', function() {
            var id = $(this).data('id');
            var row = $(this).closest('tr');
            var identificacion = row.find('td:eq(0)').text();
            var nombre = row.find('td:eq(1)').text();
            $('#caregiverId').val(id);
            $('#identificacion').val(identificacion);
            $('#nombre').val(nombre);
            $('#editModal').modal('show');

            // Obtener y seleccionar los clientes asignados
            $.ajax({
                url: 'get_caregiver_clients.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(data) {                    
                    $('#clientes').val(data.assigned_clients);                    
                }
            });
        });

        $('#saveChanges').on('click', function() {
            var formData = $('#editForm').serialize();

            $.ajax({
                url: 'update_caregiver.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response === 'success') {
                        location.reload();  // Recargar la página para reflejar los cambios
                    } else {
                        alert('Error updating caregiver');
                    }
                }
            });
        });
    });
</script>

</body>
</html>

<?php $conn->close(); ?>
