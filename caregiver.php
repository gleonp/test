<?php
include 'app/conexion.php';




$idcaregiver = isset($_GET['idcaregiver']) ? $_GET['idcaregiver'] : '';

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <link href="css/index.min.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Rochester&display=swap&family=Satisfy&display=swap" rel="stylesheet">

</head>
<body>
    <div class="container">

        <div class="admin-bar">
        <h2>Caregiver panel</h2>  
        </div>
        
        <div class="row">
        <label for="identificacion" class="form-label">Caregiver ID:</label>
        <div class="mb-3 col-lg-12">            
            <input type="text" id="identificacion" name="identificacion" class="form-control" value="<?php echo htmlspecialchars($idcaregiver); ?>" required readonly>            
        </div>        
        </div>
        
        

        <div id="filtroSemanas" style="display:none;">            
            <div class="mb-3">
                <!--<label for="filtroSemana" class="form-label" style="display:none">Select Filter:</label>
                <select id="filtroSemana" name="filtroSemana" class="form-select">
                    <option value="actual">Current Week</option>
                    <option value="personalizada">Custom Date</option>
                </select>-->
            </div>
            <div id="filtroFechas" class="row">
                <div class="mb-3 col-6">
                    <label for="fechaInicio" class="form-label">Start date:</label>
                    <input type="date" id="fechaInicio" name="fechaInicio" class="form-control">
                </div>
                <div class="mb-3 col-6">
                    <label for="fechaFin" class="form-label">Ending date:</label>
                    <input type="date" id="fechaFin" name="fechaFin" class="form-control">
                </div>
            </div>
            <div class="mb-3" id="selectClient" style="display:none;">
                <label for="cliente" class="form-label">Select Client:</label>
                <select id="cliente" name="cliente" class="form-select"></select>
            </div>
            <button type="button" id="filtrarCliente" class="btn btn-secondary" style="display: none">Search clients</button>
            <button type="button" id="filtrarSemana" class="btn btn-primary" style="display: none">View records</button>
        </div>

        
        <div id="resultados">
            <h3></h3>
        </div>
        

    </div>

    <script>


        $(document).ready(function() {

            

            $('#fechaInicio, #fechaFin').on('change', function() {
                var fechaInicio = $('#fechaInicio').val();
                var fechaFin = $('#fechaFin').val();

                if (fechaInicio && fechaFin) {
                    $('#filtrarCliente').show();
                } else {
                    $('#filtrarCliente').hide();
                }
            });

            $('#consultarEmpleado').on('click', function() {
                var identificacion = $('#identificacion').val();
                loadResults(identificacion);
            });

            $('#filtroSemana').on('change', function() {
                if ($(this).val() === 'personalizada') {
                    $('#filtroFechas').show();
                } else {
                    $('#filtroFechas').hide();
                }
            });

            $('#filtrarCliente').on('click', function() {
                var identificacion = $('#identificacion').val();
                if (identificacion) {
                    loadClients(identificacion, $('#fechaInicio').val(), $('#fechaFin').val());
                }                
            });

            $('#filtrarSemana').on('click', function() {
                var identificacion = $('#identificacion').val();
                if (identificacion) {
                    loadResults(identificacion);
                    $('#downloadPdf').show();
                }
            });

            function loadClients(identificacion, fechaInicio, fechaFin) {
                $.ajax({
                    url: 'app/consultar_clientes.php',
                    type: 'POST',
                    data: { identificacion: identificacion, fechaInicio: fechaInicio, fechaFin: fechaFin },
                    success: function(response) {
                        $('#cliente').html(response);
                        $('#selectClient').show();                        
                        if (response.includes('No clients found')) {
                            $('#filtrarSemana').hide();
                        } else {
                            $('#filtrarSemana').show();
                        }
                    }
                });
            }

            function loadResults(identificacion) {
                $.ajax({
                    url: 'app/consultar_tareas.php',
                    type: 'POST',
                    data: {
                        identificacion: identificacion,
                        filtroSemana: $('#filtroSemana').val(),
                        fechaInicio: $('#fechaInicio').val(),
                        fechaFin: $('#fechaFin').val(),
                        cliente: $('#cliente').val()
                    },
                    success: function(response) {
                        if (response.includes('Caregiver not found.')) {
                            $('#resultados').html(response);
                            $('#filtroSemanas').hide();
                            $('#downloadPdf').hide();
                        } else {
                            $('#resultados').html(response);
                            $('#filtroSemanas').show();
                            //$('#downloadPdf').show();
                        }
                    }
                });
            }

            // Cargar datos si ya hay un ID de cuidador en la URL
            if ($('#identificacion').val()) {
                loadResults($('#identificacion').val());
            }


            $('body').on('click', '.eliminar-registro', function() {
                var registroId = $(this).data('id'); // Obtener el ID del registro                
                if (confirm('Do you want to delete this record?')) {
                    $.ajax({
                        url: 'app/eliminar_registro.php', // Archivo PHP que manejará la eliminación
                        type: 'POST',
                        data: { id: registroId }, // Enviar el ID del registro
                        success: function(response) {
                            if (response === 'success') {
                                alert('Record deleted successfully.');
                                location.reload(); // Recargar la página para actualizar la tabla                                
                            } else {
                                alert('Record deleted successfully.');
                                location.reload(); // Recargar la página para actualizar la tabla
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error en la solicitud AJAX:', error);
                        }
                    });
                }
            });


        });
    </script>
</body>
</html>
