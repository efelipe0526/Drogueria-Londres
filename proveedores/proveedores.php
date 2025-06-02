<?php
// Ajusta la ruta de db.php
include '../db.php';

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

$db = new Database();

// Handle GET request to filter by id
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id']) && $isJsonRequest) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($id === false || $id <= 0) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'message' => 'Invalid ID provided']);
        $db->close();
        exit;
    }

    // Use the ID directly in the query (integer validated)
    $sql = "SELECT id, tipo, nombre, identificacion, direccion, correo, telefono FROM proveedores WHERE id = $id";
    $result = $db->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        http_response_code(200); // OK
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        http_response_code(404); // Not Found
        echo json_encode(['success' => false, 'message' => 'Proveedor no encontrado']);
    }
    $db->close();
    exit;
}

// Handle POST request to filter by id
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $isJsonRequest) {
    $contentType = isset($_SERVER['CONTENT_TYPE']) ? strtolower(trim($_SERVER['CONTENT_TYPE'])) : '';
    $input = [];

    // Log the raw body for debugging
    $rawData = file_get_contents('php://input');
    error_log("Raw POST Body: " . $rawData);
    error_log("Content-Type: " . $contentType);

    if (strpos($contentType, 'application/json') !== false) {
        $input = json_decode($rawData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400); // Bad Request
            echo json_encode(['success' => false, 'message' => 'Invalid JSON body: ' . json_last_error_msg()]);
            $db->close();
            exit;
        }
        if (empty($input)) {
            http_response_code(400); // Bad Request
            echo json_encode(['success' => false, 'message' => 'Empty JSON body']);
            $db->close();
            exit;
        }
    } else {
        http_response_code(415); // Unsupported Media Type
        echo json_encode(['success' => false, 'message' => 'Content-Type must be application/json']);
        $db->close();
        exit;
    }

    // Debug: Log the parsed input
    error_log("Parsed Input: " . print_r($input, true));

    // Check if the request is to filter by id
    if (isset($input['filter_by_id']) && isset($input['id'])) {
        $id = filter_var($input['id'], FILTER_VALIDATE_INT);
        if ($id === false || $id <= 0) {
            http_response_code(400); // Bad Request
            echo json_encode(['success' => false, 'message' => 'Invalid ID provided']);
            $db->close();
            exit;
        }

        // Use the ID directly in the query (integer validated)
        $sql = "SELECT id, tipo, nombre, identificacion, direccion, correo, telefono FROM proveedores WHERE id = $id";
        $result = $db->query($sql);

        if ($result && $row = $result->fetch_assoc()) {
            http_response_code(200); // OK
            echo json_encode(['success' => true, 'data' => $row]);
        } else {
            http_response_code(404); // Not Found
            echo json_encode(['success' => false, 'message' => 'Proveedor no encontrado']);
        }
        $db->close();
        exit;
    }
}

// Agregar proveedor
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['tipo'])) {
        // Validate and sanitize inputs
        $tipo = in_array($_POST['tipo'], ['natural', 'empresa']) ? $_POST['tipo'] : '';
        $nombre = filter_var($_POST['nombre'], FILTER_SANITIZE_STRING);
        $identificacion = filter_var($_POST['identificacion'], FILTER_SANITIZE_STRING);
        $direccion = filter_var($_POST['direccion'], FILTER_SANITIZE_STRING);
        $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
        $telefono = filter_var($_POST['telefono'], FILTER_SANITIZE_STRING);

        if (!$tipo || !$nombre || !$identificacion) {
            echo "<div class='alert alert-danger text-center'>Datos inválidos proporcionados.</div>";
        } else {
            $sql = "INSERT INTO proveedores (tipo, nombre, identificacion, direccion, correo, telefono) 
                    VALUES ('$tipo', '$nombre', '$identificacion', '$direccion', '$correo', '$telefono')";
            $db->query($sql);
        }
    } elseif (isset($_POST['editar_id'])) {
        $id = filter_var($_POST['editar_id'], FILTER_VALIDATE_INT);
        $tipo = in_array($_POST['editar_tipo'], ['natural', 'empresa']) ? $_POST['editar_tipo'] : '';
        $nombre = filter_var($_POST['editar_nombre'], FILTER_SANITIZE_STRING);
        $identificacion = filter_var($_POST['editar_identificacion'], FILTER_SANITIZE_STRING);
        $direccion = filter_var($_POST['editar_direccion'], FILTER_SANITIZE_STRING);
        $correo = filter_var($_POST['editar_correo'], FILTER_SANITIZE_EMAIL);
        $telefono = filter_var($_POST['editar_telefono'], FILTER_SANITIZE_STRING);

        if ($id === false || $id <= 0 || !$tipo || !$nombre || !$identificacion) {
            echo "<div class='alert alert-danger text-center'>ID o datos inválidos proporcionados.</div>";
        } else {
            $sql = "UPDATE proveedores SET tipo = '$tipo', nombre = '$nombre', identificacion = '$identificacion', direccion = '$direccion', correo = '$correo', telefono = '$telefono' WHERE id = $id";
            $db->query($sql);
        }
    }
}

// Eliminar proveedor
if (isset($_GET['eliminar_id'])) {
    $id = filter_var($_GET['eliminar_id'], FILTER_VALIDATE_INT);
    if ($id !== false && $id > 0) {
        $sql = "DELETE FROM proveedores WHERE id = $id";
        $db->query($sql);
    }
}

// Configuración de paginación
$limit = 10; // Número de registros por página
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Obtener el término de búsqueda si existe
$search = isset($_GET['search']) ? filter_var($_GET['search'], FILTER_SANITIZE_STRING) : '';

// Consulta para obtener los proveedores con paginación y búsqueda
$sql = "SELECT * FROM proveedores";
if ($search) {
    $sql .= " WHERE nombre LIKE '%$search%'";
}
$sql .= " LIMIT $limit OFFSET $offset";
$proveedores = $db->query($sql);

// Obtener el total de registros para la paginación
$total_sql = "SELECT COUNT(*) as total FROM proveedores";
if ($search) {
    $total_sql .= " WHERE nombre LIKE '%$search%'";
}
$total_result = $db->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total = $total_row['total'];
$total_pages = ceil($total / $limit);
?>

<?php include '../includes/header.php'; ?>

<div class="container mt-5">
    <!-- Botón para volver al menú principal -->
    <a href="../index.php" class="btn btn-secondary mb-3">Volver al Menú Principal</a>

    <h1 class="mb-4">Gestión de Proveedores</h1>

    <!-- Formulario para agregar proveedor -->
    <form method="POST" class="mb-3">
        <div class="form-group">
            <label>Tipo de Proveedor</label>
            <select name="tipo" class="form-control" required>
                <option value="natural">Persona Natural</option>
                <option value="empresa">Empresa</option>
            </select>
        </div>
        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Identificación (Cédula o NIT)</label>
            <input type="text" name="identificacion" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Dirección</label>
            <input type="text" name="direccion" class="form-control">
        </div>
        <div class="form-group">
            <label>Correo Electrónico</label>
            <input type="email" name="correo" class="form-control">
        </div>
        <div class="form-group">
            <label>Teléfono</label>
            <input type="text" name="telefono" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary mt-3">Guardar</button>
    </form>

    <!-- Formulario de búsqueda -->
    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Buscar por nombre" value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-outline-secondary">Buscar</button>
        </div>
    </form>

    <!-- Tabla de proveedores -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tipo</th>
                <th>Nombre</th>
                <th>Identificación</th>
                <th>Dirección</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $proveedores->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['tipo']; ?></td>
                    <td><?php echo $row['nombre']; ?></td>
                    <td><?php echo $row['identificacion']; ?></td>
                    <td><?php echo $row['direccion']; ?></td>
                    <td><?php echo $row['correo']; ?></td>
                    <td><?php echo $row['telefono']; ?></td>
                    <td>
                        <a href="?editar_id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                        <a href="?eliminar_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este proveedor?');">Eliminar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Paginación -->
    <nav aria-label="Page navigation">
        <ul class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>

    <!-- Formulario para editar proveedor -->
    <?php if (isset($_GET['editar_id'])): ?>
        <?php
        $id = filter_var($_GET['editar_id'], FILTER_VALIDATE_INT);
        if ($id !== false && $id > 0) {
            $sql = "SELECT * FROM proveedores WHERE id = $id";
            $proveedor = $db->query($sql)->fetch_assoc();
        } else {
            $proveedor = null;
        }
        ?>
        <?php if ($proveedor): ?>
            <h2 class="mt-5">Editar Proveedor</h2>
            <form method="POST">
                <input type="hidden" name="editar_id" value="<?php echo $proveedor['id']; ?>">
                <div class="form-group">
                    <label>Tipo de Proveedor</label>
                    <select name="editar_tipo" class="form-control" required>
                        <option value="natural" <?php echo ($proveedor['tipo'] == 'natural') ? 'selected' : ''; ?>>Persona Natural</option>
                        <option value="empresa" <?php echo ($proveedor['tipo'] == 'empresa') ? 'selected' : ''; ?>>Empresa</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="editar_nombre" class="form-control" value="<?php echo htmlspecialchars($proveedor['nombre']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Identificación (Cédula o NIT)</label>
                    <input type="text" name="editar_identificacion" class="form-control" value="<?php echo htmlspecialchars($proveedor['identificacion']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Dirección</label>
                    <input type="text" name="editar_direccion" class="form-control" value="<?php echo htmlspecialchars($proveedor['direccion']); ?>">
                </div>
                <div class="form-group">
                    <label>Correo Electrónico</label>
                    <input type="email" name="editar_correo" class="form-control" value="<?php echo htmlspecialchars($proveedor['correo']); ?>">
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="editar_telefono" class="form-control" value="<?php echo htmlspecialchars($proveedor['telefono']); ?>">
                </div>
                <button type="submit" class="btn btn-primary mt-3">Guardar Cambios</button>
                <a href="proveedores.php" class="btn btn-secondary mt-3">Cancelar</a>
            </form>
        <?php else: ?>
            <div class="alert alert-danger text-center">Proveedor no encontrado.</div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
<?php $db->close(); ?>