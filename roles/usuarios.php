<?php

// Enable PHP error reporting for debugging

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);



include '../db.php'; // Ruta corregida para apuntar a la raíz del proyecto



$db = new Database();



// Check if the request expects a JSON response

$acceptHeader = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';

$isJsonRequest = (strpos($acceptHeader, 'application/json') !== false) || (isset($_GET['format']) && $_GET['format'] === 'json');



if ($isJsonRequest) {

    header('Content-Type: application/json');
}



// Handle GET request for JSON data

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $isJsonRequest) {

    $usuarios = $db->query("SELECT u.id, u.username, r.nombre AS rol FROM usuarios u JOIN roles r ON u.rol_id = r.id");



    if ($usuarios === false) {

        echo json_encode(['success' => false, 'message' => 'Error en la consulta: ' . $db->conn->error]);

        exit;
    }



    $users = [];

    while ($row = $usuarios->fetch_assoc()) {

        $users[] = [

            'id' => $row['id'],

            'username' => $row['username'],

            'rol' => $row['rol']

        ];
    }



    echo json_encode([

        'success' => true,

        'data' => $users

    ]);

    exit;
}



// Handle POST request for JSON data

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isJsonRequest) {

    $contentType = isset($_SERVER['CONTENT_TYPE']) ? strtolower($_SERVER['CONTENT_TYPE']) : 'application/json';

    $raw_input = file_get_contents('php://input');



    if (strpos($contentType, 'application/json') !== false) {

        $input = json_decode($raw_input, true) ?? [];
    } elseif (strpos($contentType, 'application/x-www-form-urlencoded') !== false || strpos($contentType, 'multipart/form-data') !== false) {

        $input = $_POST;
    } else {

        $input = [];

        echo json_encode(['success' => false, 'message' => 'Unsupported Content-Type: ' . $contentType]);

        exit;
    }



    // Debug: Log the content type, raw input, and parsed input

    error_log("Content-Type: " . $contentType);

    error_log("Raw input: " . $raw_input);

    error_log("Parsed input: " . print_r($input, true));



    $username = $input['username'] ?? '';

    $password = $input['password'] ?? '';

    $rol_id = $input['rol_id'] ?? '';



    if (empty($username) || empty($rol_id)) {

        echo json_encode(['success' => false, 'message' => 'Faltan campos requeridos (username o rol_id). Debug: username=' . $username . ', rol_id=' . $rol_id . ', raw_input=' . $raw_input]);

        exit;
    }



    // Verificar si el nombre de usuario ya existe

    $sql_check = "SELECT id FROM usuarios WHERE username = '$username'";

    $result = $db->query($sql_check);



    if ($result->num_rows > 0) {

        echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya está en uso. Por favor, elige otro.']);

        exit;
    } else {

        $password_hash = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;

        $sql = "INSERT INTO usuarios (username, password, rol_id) VALUES ('$username', '$password_hash', $rol_id)";

        if ($db->query($sql) === true) {

            $new_id = $db->conn->insert_id;

            echo json_encode([

                'success' => true,

                'message' => 'Usuario agregado correctamente.',

                'data' => ['id' => $new_id, 'username' => $username, 'rol_id' => $rol_id]

            ]);
        } else {

            echo json_encode(['success' => false, 'message' => 'Error al agregar el usuario: ' . $db->conn->error]);
        }

        exit;
    }
}



// Agregar usuario

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'])) {

    $username = $_POST['username'];

    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    $rol_id = $_POST['rol_id'];



    // Verificar si el nombre de usuario ya existe

    $sql_check = "SELECT id FROM usuarios WHERE username = '$username'";

    $result = $db->query($sql_check);



    if ($result->num_rows > 0) {

        echo "<div class='alert alert-danger'>El nombre de usuario ya está en uso. Por favor, elige otro.</div>";
    } else {

        // Insertar el nuevo usuario

        $sql = "INSERT INTO usuarios (username, password, rol_id) VALUES ('$username', '$password', $rol_id)";

        $db->query($sql);

        echo "<div class='alert alert-success'>Usuario agregado correctamente.</div>";
    }
}



// Eliminar usuario

if (isset($_GET['eliminar'])) {

    $usuario_id = $_GET['eliminar'];



    // Eliminar registros relacionados en usuario_modulos

    $db->query("DELETE FROM usuario_modulos WHERE usuario_id = $usuario_id");



    // Eliminar el usuario

    $db->query("DELETE FROM usuarios WHERE id = $usuario_id");



    header("Location: usuarios.php"); // Recargar la página

    exit();
}



// Obtener usuarios y roles

$usuarios = $db->query("SELECT u.id, u.username, r.nombre AS rol FROM usuarios u JOIN roles r ON u.rol_id = r.id");

$roles = $db->query("SELECT * FROM roles");

?>



<?php include '../includes/header.php'; ?>



<div class="container mt-5">

    <!-- Enlace para volver al menú principal -->

    <a href="modulo_roles.php" class="btn btn-secondary mb-3">Volver al Menú Principal</a>

    <h1 class="mb-4">Gestión de Usuarios</h1>



    <!-- Formulario para agregar usuario -->

    <form method="POST" class="mb-3">

        <div class="form-group">

            <label>Nombre de Usuario</label>

            <input type="text" name="username" class="form-control" required>

        </div>

        <div class="form-group">

            <label>Contraseña (dejar en blanco para no asignar)</label>

            <input type="password" name="password" class="form-control">

        </div>

        <div class="form-group">

            <label>Rol</label>

            <select name="rol_id" class="form-control" required>

                <?php while ($row = $roles->fetch_assoc()): ?>

                    <option value="<?php echo $row['id']; ?>"><?php echo $row['nombre']; ?></option>

                <?php endwhile; ?>

            </select>

        </div>

        <button type="submit" class="btn btn-primary mt-3">Agregar Usuario</button>

    </form>



    <!-- Tabla de usuarios -->

    <table class="table table-striped">

        <thead>

            <tr>

                <th>ID</th>

                <th>Nombre de Usuario</th>

                <th>Rol</th>

                <th>Acciones</th>

            </tr>

        </thead>

        <tbody>

            <?php while ($row = $usuarios->fetch_assoc()): ?>

                <tr>

                    <td><?php echo $row['id']; ?></td>

                    <td><?php echo $row['username']; ?></td>

                    <td><?php echo $row['rol']; ?></td>

                    <td>

                        <!-- Botón para asignar módulos -->

                        <a href="asignar_modulos.php?usuario_id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">Asignar Módulos</a>

                        <!-- Botón para editar -->

                        <a href="editar_usuario.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Editar</a>

                        <!-- Botón para eliminar -->

                        <a href="usuarios.php?eliminar=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este usuario?');">Eliminar</a>

                    </td>

                </tr>

            <?php endwhile; ?>

        </tbody>

    </table>

</div>



<?php include '../includes/footer.php'; ?>