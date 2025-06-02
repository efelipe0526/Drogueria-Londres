<?php
header('Content-Type: application/json');

include 'db.php';

$db = new Database();
$conexion = $db->getConnection();

$codigo = $_GET['codigo'] ?? '';
$codigo = trim($codigo); // Keep the original input for comparison

if (empty($codigo)) {
    echo json_encode(['error' => 'Código de barras no proporcionado']);
    $conexion->close();
    exit;
}

try {
    // Try exact match first
    $stmt = $conexion->prepare("SELECT id, nombre, precio, precio_por_unidad, categoria_id 
                                FROM productos 
                                WHERE codigo_barras = ?");
    $stmt->bind_param("s", $codigo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // If exact match fails, try cleaning the barcode
        $cleaned_codigo = preg_replace('/[^0-9]/', '', $codigo);
        $stmt = $conexion->prepare("SELECT id, nombre, precio, precio_por_unidad, categoria_id 
                                    FROM productos 
                                    WHERE REPLACE(codigo_barras, ' ', '') = ?");
        $stmt->bind_param("s", $cleaned_codigo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(['error' => 'Producto no encontrado para el código: ' . $codigo]);
        } else {
            $producto = $result->fetch_assoc();
            echo json_encode(['producto' => $producto]);
        }
    } else {
        $producto = $result->fetch_assoc();
        echo json_encode(['producto' => $producto]);
    }
} catch (Exception $e) {
    error_log("Error en obtener_producto_por_codigo.php: " . $e->getMessage());
    echo json_encode(['error' => 'Error al procesar el código de barras: ' . $e->getMessage()]);
}

$stmt->close();
$conexion->close();
