<?php
// Check if the request expects a JSON response
$acceptHeader = isset($_SERVER['HTTP_ACCEPT']) ? strtolower(trim($_SERVER['HTTP_ACCEPT'])) : '';
$isJsonRequest = strpos($acceptHeader, 'application/json') !== false;

// Debug: Log the HTTP_ACCEPT header
error_log("HTTP_ACCEPT: " . (isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : 'Not set'));

// Fallback: Check for ?format=json in the URL
if (isset($_GET['format']) && $_GET['format'] === 'json') {
    $isJsonRequest = true;
    header('Content-Type: application/json; charset=UTF-8');
}

// Debug: Log the $isJsonRequest value
error_log("isJsonRequest: " . ($isJsonRequest ? 'true' : 'false'));

// Force Content-Type for JSON requests
if ($isJsonRequest) {
    header('Content-Type: application/json; charset=UTF-8');
}

// Handle JSON requests first and exit if matched
$mysqli = new mysqli("localhost", "root", "", "marci");

// Verificar conexión
if ($mysqli->connect_error) {
    if ($isJsonRequest) {
        echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $mysqli->connect_error]);
        exit;
    }
    die("Error de conexión: " . $mysqli->connect_error);
}

// Handle GET request to filter by id
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id']) && $isJsonRequest) {
    $id = $_GET['id'];
    $query = "SELECT r.id, r.numero_factura, r.fecha, r.subtotal, r.impuesto, r.total_con_impuesto, f.tipo, f.banco, f.cuenta, r.monto_efectivo, r.monto_otra_forma_pago 
              FROM reportes r 
              LEFT JOIN formas_pago f ON r.forma_pago_id = f.id 
              WHERE r.id = $id";
    $result = $mysqli->query($query);

    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Reporte no encontrado']);
    }
    $mysqli->close();
    exit;
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // For JSON requests, read the body if Content-Type is application/json
    $contentType = isset($_SERVER['CONTENT_TYPE']) ? strtolower(trim($_SERVER['CONTENT_TYPE'])) : '';
    $input = [];

    // Log the raw body for debugging
    $rawData = file_get_contents('php://input');
    error_log("Raw POST Body: " . $rawData);
    error_log("Content-Type: " . $contentType);

    if ($isJsonRequest) {
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode($rawData, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400); // Bad Request
                echo json_encode(['success' => false, 'message' => 'Invalid JSON body: ' . json_last_error_msg()]);
                $mysqli->close();
                exit;
            }
            if (empty($input)) {
                http_response_code(400); // Bad Request
                echo json_encode(['success' => false, 'message' => 'Empty JSON body']);
                $mysqli->close();
                exit;
            }
        } else {
            http_response_code(415); // Unsupported Media Type
            echo json_encode(['success' => false, 'message' => 'Content-Type must be application/json']);
            $mysqli->close();
            exit;
        }
    } else {
        $input = $_POST;
    }

    // Debug: Log the parsed input
    error_log("Parsed Input: " . print_r($input, true));

    // Check if the request is to filter by id
    if ($isJsonRequest && isset($input['filter_by_id']) && isset($input['id'])) {
        $id = filter_var($input['id'], FILTER_VALIDATE_INT);
        if ($id === false || $id <= 0) {
            http_response_code(400); // Bad Request
            echo json_encode(['success' => false, 'message' => 'Invalid ID provided']);
            $mysqli->close();
            exit;
        }

        $query = "SELECT r.id, r.numero_factura, r.fecha, r.subtotal, r.impuesto, r.total_con_impuesto, f.tipo, f.banco, f.cuenta, r.monto_efectivo, r.monto_otra_forma_pago 
                  FROM reportes r 
                  LEFT JOIN formas_pago f ON r.forma_pago_id = f.id 
                  WHERE r.id = ?";

        if ($stmt = $mysqli->prepare($query)) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                http_response_code(200); // OK
                echo json_encode(['success' => true, 'data' => $row]);
            } else {
                http_response_code(404); // Not Found
                echo json_encode(['success' => false, 'message' => 'Reporte no encontrado']);
            }
            $stmt->close();
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(['success' => false, 'message' => 'Error preparing query: ' . $mysqli->error]);
        }
        $mysqli->close();
        exit;
    }

    // Existing POST logic for reiniciar and editar
    if (isset($input['reiniciar'])) {
        // Reiniciar la tabla reportes
        $query = "DELETE FROM reportes";
        if ($mysqli->query($query)) {
            echo "<div class='alert alert-success text-center'>Todos los registros de reportes han sido eliminados.</div>";
        } else {
            echo "<div class='alert alert-danger text-center'>Error al eliminar los registros de reportes: " . $mysqli->error . "</div>";
        }

        // Reiniciar la tabla ventas
        $query_ventas = "DELETE FROM ventas";
        if ($mysqli->query($query_ventas)) {
            echo "<div class='alert alert-success text-center'>Todos los registros de ventas han sido eliminados.</div>";
        } else {
            echo "<div class='alert alert-danger text-center'>Error al eliminar los registros de ventas: " . $mysqli->error . "</div>";
        }
    }

    if (isset($input['editar'])) {
        $id = filter_var($input['id'], FILTER_VALIDATE_INT);
        $numero_factura = $mysqli->real_escape_string($input['numero_factura']);
        $fecha = $mysqli->real_escape_string($input['fecha']);
        $subtotal = filter_var($input['subtotal'], FILTER_VALIDATE_FLOAT);
        $impuesto = filter_var($input['impuesto'], FILTER_VALIDATE_FLOAT);
        $total_con_impuesto = filter_var($input['total_con_impuesto'], FILTER_VALIDATE_FLOAT);
        $forma_pago_id = filter_var($input['forma_pago_id'], FILTER_VALIDATE_INT);
        $monto_efectivo = isset($input['monto_efectivo']) ? filter_var($input['monto_efectivo'], FILTER_VALIDATE_FLOAT) : 0;
        $monto_otra_forma_pago = isset($input['monto_otra_forma_pago']) ? filter_var($input['monto_otra_forma_pago'], FILTER_VALIDATE_FLOAT) : 0;

        if ($id === false || $subtotal === false || $impuesto === false || $total_con_impuesto === false || $forma_pago_id === false) {
            echo "<div class='alert alert-danger text-center'>Datos inválidos proporcionados.</div>";
        } else {
            $query = "UPDATE reportes SET 
                      numero_factura = ?, 
                      fecha = ?, 
                      subtotal = ?, 
                      impuesto = ?, 
                      total_con_impuesto = ?, 
                      forma_pago_id = ?,
                      monto_efectivo = ?,
                      monto_otra_forma_pago = ?
                      WHERE id = ?";

            if ($stmt = $mysqli->prepare($query)) {
                $stmt->bind_param('ssdddiidd', $numero_factura, $fecha, $subtotal, $impuesto, $total_con_impuesto, $forma_pago_id, $monto_efectivo, $monto_otra_forma_pago, $id);
                if ($stmt->execute()) {
                    echo "<div class='alert alert-success text-center'>El reporte ha sido actualizado correctamente.</div>";
                } else {
                    echo "<div class='alert alert-danger text-center'>Error al actualizar el reporte: " . $stmt->error . "</div>";
                }
                $stmt->close();
            } else {
                echo "<div class='alert alert-danger text-center'>Error al preparar la consulta: " . $mysqli->error . "</div>";
            }
        }
    }
}

// Existing GET logic for eliminar
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $query = "DELETE FROM reportes WHERE id = $id";
    if ($mysqli->query($query)) {
        echo "<div class='alert alert-success text-center'>El reporte ha sido eliminado correctamente.</div>";
    } else {
        echo "<div class='alert alert-danger text-center'>Error al eliminar el reporte: " . $mysqli->error . "</div>";
    }
}

// Configuración de paginación
$registros_por_pagina = 10;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina - 1) * $registros_por_pagina;

// Obtener el total de registros para la paginación
$query_total = "SELECT COUNT(*) as total FROM reportes";
$result_total = $mysqli->query($query_total);
$total_registros = $result_total->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Obtener el número de factura para buscar
$numero_factura_buscar = isset($_GET['numero_factura_buscar']) ? $_GET['numero_factura_buscar'] : '';

// Obtener los datos de los reportes con paginación y búsqueda
$query = "SELECT r.id, r.numero_factura, r.fecha, r.subtotal, r.impuesto, r.total_con_impuesto, f.tipo, f.banco, f.cuenta, r.monto_efectivo, r.monto_otra_forma_pago 
          FROM reportes r
          LEFT JOIN formas_pago f ON r.forma_pago_id = f.id
          WHERE r.numero_factura LIKE '%$numero_factura_buscar%'
          ORDER BY r.fecha DESC
          LIMIT $offset, $registros_por_pagina";
$result = $mysqli->query($query);

// Obtener los datos de un reporte para editar
$reporte_editar = null;
if (isset($_GET['editar'])) {
    $id = $_GET['editar'];
    $reporte_editar = $mysqli->query("SELECT * FROM reportes WHERE id = $id")->fetch_assoc();
}

// Obtener las formas de pago
$formas_pago = $mysqli->query("SELECT * FROM formas_pago");
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Reportes</title>

    <!-- Bootstrap CSS -->
    <link href="boot/bootstrap.min.css" rel="stylesheet">
    <!-- Estilos personalizados -->
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            margin-top: 50px;
        }

        .table {
            margin-top: 20px;
        }

        .table thead {
            background-color: #007bff;
            color: white;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        h1 {
            color: #007bff;
            text-align: center;
            margin-bottom: 30px;
        }

        .btn-reiniciar {
            margin-top: 20px;
        }

        .pagination {
            justify-content: center;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="index.php" class="btn btn-secondary mb-3">Volver al Menú Principal</a>
        <h1>Gestión de Reportes</h1>

        <!-- Formulario de búsqueda por número de factura -->
        <form method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-6">
                    <input type="text" name="numero_factura_buscar" class="form-control" placeholder="Buscar por número de factura" value="<?php echo htmlspecialchars($numero_factura_buscar); ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Buscar</button>
                </div>
                <div class="col-md-3">
                    <a href="reportes.php" class="btn btn-secondary w-100">Limpiar Búsqueda</a>
                </div>
            </div>
        </form>

        <!-- Botón para reiniciar la tabla reportes y ventas -->
        <form method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar todos los registros de reportes y ventas?');">
            <button type="submit" name="reiniciar" class="btn btn-danger btn-reiniciar">Reiniciar Reportes y Ventas</button>
        </form>

        <!-- Botones para ver otras formas de pago y pagos en efectivo -->
        <div class="d-flex gap-2 mb-3"> <!-- Contenedor flexible con espacio entre botones -->
            <a href="reportes_otras_formas_pago.php" class="btn btn-info">
                Ver Otras Formas de Pago
            </a>
            <a href="reportes_efectivo.php" class="btn btn-info">
                Ver Pagos en Efectivo
            </a>
        </div>

        <!-- Contenedor para la tabla dinámica -->
        <div id="tablaOtrasFormasPago" class="mt-4" style="display: none;">
            <h3>Reporte de Otras Formas de Pago</h3>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Número de Factura</th>
                        <th>Banco</th>
                        <th>Cuenta</th>
                        <th>Valor Total con Impuesto</th>
                        <th>Valor Otra Forma de Pago</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTablaOtrasFormasPago">
                    <!-- Aquí se cargarán los datos dinámicamente -->
                </tbody>
            </table>
            <div class="mt-3">
                <strong>Total por Día:</strong> <span id="totalDia">0</span><br>
                <strong>Total por Mes:</strong> <span id="totalMes">0</span><br>
                <strong>Total Acumulado:</strong> <span id="totalAcumulado">0</span>
            </div>
        </div>

        <!-- Formulario para editar un reporte -->
        <?php if (isset($_GET['editar'])): ?>
            <h2 class="mt-4">Editar Reporte</h2>
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $reporte_editar['id']; ?>">
                <div class="form-group">
                    <label>Número de Factura</label>
                    <input type="text" name="numero_factura" class="form-control" value="<?php echo $reporte_editar['numero_factura']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Fecha</label>
                    <input type="date" name="fecha" class="form-control" value="<?php echo $reporte_editar['fecha']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Subtotal</label>
                    <input type="number" step="0.01" name="subtotal" class="form-control" value="<?php echo $reporte_editar['subtotal']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Impuesto</label>
                    <input type="number" step="0.01" name="impuesto" class="form-control" value="<?php echo $reporte_editar['impuesto']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Total con Impuesto</label>
                    <input type="number" step="0.01" name="total_con_impuesto" class="form-control" value="<?php echo $reporte_editar['total_con_impuesto']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Forma de Pago</label>
                    <select name="forma_pago_id" class="form-control" required>
                        <option value="">-- Seleccione una forma de pago --</option>
                        <?php
                        while ($forma_pago = $formas_pago->fetch_assoc()) {
                            $selected = ($reporte_editar['forma_pago_id'] == $forma_pago['id']) ? 'selected' : '';
                            echo "<option value='{$forma_pago['id']}' $selected>{$forma_pago['tipo']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Monto en Efectivo</label>
                    <input type="number" step="0.01" name="monto_efectivo" class="form-control" value="<?php echo $reporte_editar['monto_efectivo']; ?>">
                </div>
                <div class="form-group">
                    <label>Monto en Otra Forma de Pago</label>
                    <input type="number" step="0.01" name="monto_otra_forma_pago" class="form-control" value="<?php echo $reporte_editar['monto_otra_forma_pago']; ?>">
                </div>
                <button type="submit" name="editar" class="btn btn-primary mt-3">Guardar Cambios</button>
                <a href="reportes.php" class="btn btn-secondary mt-3">Cancelar</a>
            </form>
        <?php endif; ?>

        <!-- Tabla de reportes -->
        <?php if ($result->num_rows > 0) : ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Número Factura</th>
                            <th>Fecha</th>
                            <th>Subtotal</th>
                            <th>Impuesto</th>
                            <th>Total con Impuesto</th>
                            <th>Forma de Pago</th>
                            <th>Banco</th>
                            <th>Cuenta</th>
                            <th>Monto Efectivo</th>
                            <th>Monto Otra Forma de Pago</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) : ?>
                            <tr>
                                <td><?php echo $row['numero_factura']; ?></td>
                                <td><?php echo $row['fecha']; ?></td>
                                <td><?php echo number_format($row['subtotal'], 2); ?></td>
                                <td><?php echo number_format($row['impuesto'], 2); ?></td>
                                <td><?php echo number_format($row['total_con_impuesto'], 2); ?></td>
                                <td><?php echo $row['tipo']; ?></td>
                                <td><?php echo $row['banco']; ?></td>
                                <td><?php echo $row['cuenta']; ?></td>
                                <td><?php echo number_format($row['monto_efectivo'], 2); ?></td>
                                <td><?php echo number_format($row['monto_otra_forma_pago'], 2); ?></td>
                                <td>
                                    <a href="?editar=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                    <a href="?eliminar=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este reporte?')">Eliminar</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <nav aria-label="Paginación">
                <ul class="pagination">
                    <?php if ($pagina > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?pagina=<?= $pagina - 1; ?>&numero_factura_buscar=<?= $numero_factura_buscar; ?>" aria-label="Anterior">
                                <span aria-hidden="true">«</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <li class="page-item <?= ($pagina == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?pagina=<?= $i; ?>&numero_factura_buscar=<?= $numero_factura_buscar; ?>"><?= $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($pagina < $total_paginas): ?>
                        <li class="page-item">
                            <a class="page-link" href="?pagina=<?= $pagina + 1; ?>&numero_factura_buscar=<?= $numero_factura_buscar; ?>" aria-label="Siguiente">
                                <span aria-hidden="true">»</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php else : ?>
            <div class="alert alert-info" role="alert">
                No hay registros en la tabla `reportes`.
            </div>
        <?php endif; ?>
    </div>

</body>

</html>

<?php
// Cerrar conexión
$mysqli->close();
?>