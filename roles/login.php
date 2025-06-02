<?php
// Enable PHP error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Check if the request expects a JSON response
$acceptHeader = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
$isJsonRequest = (strpos($acceptHeader, 'application/json') !== false) || (isset($_GET['format']) && $_GET['format'] === 'json');

if ($isJsonRequest) {
    header('Content-Type: application/json');
}

// Verificar si el usuario ya está logueado
if (isset($_SESSION['usuario_id'])) {
    if ($isJsonRequest) {
        echo json_encode(['success' => true, 'message' => 'Usuario ya está logueado']);
        exit;
    } else {
        header("Location: ../index.php"); // Redirigir al panel principal
        exit();
    }
}

// Include database connection
include '../db.php';
$db = new Database();

// Handle GET or POST request with id parameter for JSON data
if (in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST']) && isset($_REQUEST['id']) && $isJsonRequest) {
    $id = (int)$_REQUEST['id'];
    $sql = "SELECT u.id, u.username, r.nombre AS rol 
            FROM usuarios u 
            JOIN roles r ON u.rol_id = r.id 
            WHERE u.id = $id";
    $result = $db->query($sql);

    // Check for query errors
    if ($result === false) {
        echo json_encode(['success' => false, 'message' => 'Error en la consulta: ' . $db->conn->error]);
        exit;
    }

    if ($result->num_rows == 1) {
        $usuario = $result->fetch_assoc();

        // Obtener los módulos asignados al usuario
        $modulos_asignados = $db->query("SELECT modulo_id FROM usuario_modulos WHERE usuario_id = $id");
        $modulos = [];
        while ($row = $modulos_asignados->fetch_assoc()) {
            $modulos[] = $row['modulo_id'];
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'id' => $usuario['id'],
                'username' => $usuario['username'],
                'rol' => $usuario['rol'],
                'modulos' => $modulos
            ]
        ]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }
}

// Procesar el formulario de inicio de sesión
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
    // For JSON requests, read the body if Content-Type is application/json
    $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
    if ($isJsonRequest && strpos($contentType, 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true);
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';
    } else {
        $username = $_POST['username'];
        $password = $_POST['password'];
    }

    // Buscar el usuario en la base de datos
    $sql = "SELECT u.id, u.username, u.password, r.nombre AS rol 
            FROM usuarios u 
            JOIN roles r ON u.rol_id = r.id 
            WHERE u.username = '$username'";
    $result = $db->query($sql);

    if ($result->num_rows == 1) {
        $usuario = $result->fetch_assoc();

        // Verificar la contraseña
        if (password_verify($password, $usuario['password'])) {
            // Iniciar sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['username'] = $usuario['username'];
            $_SESSION['rol'] = $usuario['rol']; // Guardar el nombre del rol en la sesión

            // Obtener los módulos asignados al usuario
            $modulos_asignados = $db->query("SELECT modulo_id FROM usuario_modulos WHERE usuario_id = {$usuario['id']}");
            $modulos = [];
            while ($row = $modulos_asignados->fetch_assoc()) {
                $modulos[] = $row['modulo_id'];
            }
            $_SESSION['modulos_asignados'] = $modulos; // Almacenar módulos en la sesión

            if ($isJsonRequest) {
                echo json_encode(['success' => true, 'message' => 'Inicio de sesión exitoso', 'usuario' => $usuario]);
                exit;
            } else {
                header("Location: ../index.php"); // Redirigir al panel principal
                exit();
            }
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "Usuario no encontrado.";
    }

    if ($isJsonRequest) {
        echo json_encode(['success' => false, 'message' => $error]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: hsl(193, 43.70%, 68.60%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }

        .login-container img {
            max-width: 100%;
            height: auto;
            margin-bottom: 20px;
        }

        .login-container h1 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        .login-container .form-group {
            margin-bottom: 15px;
        }

        .login-container .btn {
            width: 100%;
        }

        .error-message {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <!-- Mensaje de bienvenida -->
        <h1>Bienvenidos al Sistema de Drogueria</h1>

        <!-- Imagen -->
        <img src="../img/londres.jpg" alt="Imagen de bienvenida">

        <!-- Mostrar mensaje de error si existe -->
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Formulario de inicio de sesión -->
        <form method="POST">
            <div class="form-group">
                <label for="username">Nombre de Usuario</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
        </form>
    </div>
</body>

</html>