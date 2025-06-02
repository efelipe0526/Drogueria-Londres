<?php
header('Content-Type: application/json');

include 'db.php';

$db = new Database();
$conexion = $db->getConnection();

$codigo = $_GET['codigo'] ?? '';

if (empty($codigo)) {
    echo json_encode(['error' => 'Código de barras no proporcionado']);
    $conexion->close();
    exit;
}

try {
    $stmt = $conexion->prepare("SELECT id, nombre, precio, precio_por_unidad, unidad, categoria_id 
                                FROM productos 
                                WHERE codigo_barras = ?");
    $stmt->bind_param("s", $codigo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['error' => 'Producto no encontrado']);
    } else {
        $producto = $result->fetch_assoc();
        echo json_encode(['producto' => $producto]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al procesar el código de barras: ' . $e->getMessage()]);
}

$stmt->close();
$conexion->close();
