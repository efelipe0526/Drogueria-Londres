<?php
// Enable JSON response if requested
header('Content-Type: application/json');
$isJsonRequest = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

include '../db.php';

$db = new Database();

$usuario_id = $_GET['id'] ?? null;

// Obtener la información del usuario
$usuario = null;
if ($usuario_id) {
    $result = $db->query("SELECT * FROM usuarios WHERE id = $usuario_id");
    $usuario = $result->fetch_assoc();
}

// Procesar el formulario de edición
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = [];
    if ($isJsonRequest && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
    } else {
        $input = $_POST;
    }

    $username = $input['username'] ?? '';
    $password = !empty($input['password']) ? password_hash($input['password'], PASSWORD_DEFAULT) : ($usuario['password'] ?? '');
    $rol_id = $input['rol_id'] ?? 0;

    if (empty($username) || empty($rol_id) || !$usuario_id) {
        $response = ['success' => false, 'message' => 'Faltan campos requeridos o ID de usuario inválido'];
    } else {
        $db->query("UPDATE usuarios SET username = '$username', password = '$password', rol_id = $rol_id WHERE id = $usuario_id");
        $response = ['success' => true, 'message' => 'Usuario actualizado correctamente'];
    }
    if ($isJsonRequest) {
        echo json_encode($response);
        exit;
    } else {
        echo "<div class='alert alert-success'>Usuario actualizado correctamente.</div>";
    }
}

// Obtener roles
$roles = $db->query("SELECT * FROM roles");

// Return JSON for GET if requested
if ($isJsonRequest && $usuario) {
    echo json_encode(['success' => true, 'data' => $usuario]);
    exit;
}
?>

<?php include '../includes/header.php'; ?>

<div class="container mt-5">
    <h1 class="mb-4">Editar Usuario</h1>

    <form method="POST">
        <div class="form-group">
            <label>Nombre de Usuario:</label>
            <input type="text" name="username" class="form-control" value="<?php echo $usuario['username']; ?>" required>
        </div>
        <div class="form-group">
            <label>Contraseña (dejar en blanco para no cambiar):</label>
            <input type="password" name="password" class="form-control">
        </div>
        <div class="form-group">
            <label>Rol:</label>
            <select name="rol_id" class="form-control" required>
                <?php while ($row = $roles->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>" <?php echo $row['id'] == $usuario['rol_id'] ? 'selected' : ''; ?>><?php echo $row['nombre']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>