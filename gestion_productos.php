<?php
// Enable JSON response if requested
header('Content-Type: application/json');
$isJsonRequest = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

include 'db.php';
include 'includes/header.php';

$db = new Database();
$conexion = $db->getConnection();

$categorias = $db->query("SELECT * FROM categorias");
$laboratorios = $db->query("SELECT * FROM laboratorios");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input = [];
    if ($isJsonRequest && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
    } else {
        $input = $_POST;
    }

    $nombre = $input['nombre'] ?? '';
    $categoria_id = intval($input['categoria_id'] ?? 0);
    $codigo_barras = $input['codigo_barras'] ?? '';
    $precio = floatval($input['precio'] ?? 0);
    $precio_por_unidad = floatval($input['precio_por_unidad'] ?? 0);
    $unidad = $input['unidad'] ?? '';
    $stock_cajas = intval($input['stock_cajas'] ?? 0);
    $unidades_por_caja = intval($input['unidades_por_caja'] ?? 0);
    $unidades_restantes = intval($input['unidades_restantes'] ?? 0);
    $laboratorio_id = intval($input['laboratorio_id'] ?? 0);

    // Validar datos
    if (empty($nombre) || $categoria_id <= 0 || $laboratorio_id <= 0) {
        $response = ['success' => false, 'message' => 'Todos los campos obligatorios deben estar completos.'];
    } else {
        $stmt = $conexion->prepare("INSERT INTO productos (nombre, categoria_id, codigo_barras, precio, precio_por_unidad, unidad, stock_cajas, unidades_por_caja, unidades_restantes) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sisddssii", $nombre, $categoria_id, $codigo_barras, $precio, $precio_por_unidad, $unidad, $stock_cajas, $unidades_por_caja, $unidades_restantes);
        if ($stmt->execute()) {
            $producto_id = $conexion->insert_id;
            $stmt_lab = $conexion->prepare("INSERT INTO producto_laboratorio (producto_id, laboratorio_id) VALUES (?, ?)");
            $stmt_lab->bind_param("ii", $producto_id, $laboratorio_id);
            if ($stmt_lab->execute()) {
                $response = ['success' => true, 'message' => 'Producto registrado correctamente.'];
            } else {
                $response = ['success' => false, 'message' => 'Error al asociar el laboratorio.'];
            }
            $stmt_lab->close();
        } else {
            $response = ['success' => false, 'message' => 'Error al registrar el producto: ' . $stmt->error];
        }
        $stmt->close();
    }
    if ($isJsonRequest) {
        echo json_encode($response);
        exit;
    } else {
        echo "<script>Swal.fire({icon: 'success', title: 'Éxito', text: 'Producto registrado correctamente.'}).then(() => {window.location.href = 'gestion_productos.php'});</script>";
    }
}
?>

<div class="container mt-5">
    <h1 class="text-center mb-4">Gestión de Productos</h1>
    <form method="POST">
        <div class="form-group">
            <label for="nombre">Nombre del Producto:</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required>
        </div>
        <div class="form-group">
            <label for="categoria_id">Categoría:</label>
            <select class="form-control" id="categoria_id" name="categoria_id" required>
                <option value="">-- Seleccione una categoría --</option>
                <?php while ($categoria = $categorias->fetch_assoc()): ?>
                    <option value="<?php echo $categoria['id']; ?>" data-usa-cajas="<?php echo $categoria['usa_cajas']; ?>">
                        <?php echo htmlspecialchars($categoria['nombre']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="codigo_barras">Código de Barras:</label>
            <input type="text" class="form-control" id="codigo_barras" name="codigo_barras">
        </div>
        <div class="form-group">
            <label for="precio">Precio (por Caja o Unidad Total):</label>
            <input type="number" step="0.01" class="form-control" id="precio" name="precio" required>
        </div>
        <div class="form-group">
            <label for="precio_por_unidad">Precio por Unidad:</label>
            <input type="number" step="0.01" class="form-control" id="precio_por_unidad" name="precio_por_unidad">
        </div>
        <div class="form-group">
            <label for="unidad">Unidad (ej. caja, unidad):</label>
            <input type="text" class="form-control" id="unidad" name="unidad">
        </div>
        <div class="form-group" id="stock_cajas_group" style="display: none;">
            <label for="stock_cajas">Stock de Cajas:</label>
            <input type="number" class="form-control" id="stock_cajas" name="stock_cajas" value="0">
        </div>
        <div class="form-group" id="unidades_por_caja_group" style="display: none;">
            <label for="unidades_por_caja">Unidades por Caja:</label>
            <input type="number" class="form-control" id="unidades_por_caja" name="unidades_por_caja" value="0">
        </div>
        <div class="form-group">
            <label for="unidades_restantes">Unidades Restantes (Stock de Unidades):</label>
            <input type="number" class="form-control" id="unidades_restantes" name="unidades_restantes" value="0">
        </div>
        <div class="form-group">
            <label for="laboratorio_id">Laboratorio:</label>
            <select class="form-control" id="laboratorio_id" name="laboratorio_id" required>
                <option value="">-- Seleccione un laboratorio --</option>
                <?php while ($laboratorio = $laboratorios->fetch_assoc()): ?>
                    <option value="<?php echo $laboratorio['id']; ?>">
                        <?php echo htmlspecialchars($laboratorio['nombre']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Registrar Producto</button>
    </form>
</div>

<script>
    document.getElementById('categoria_id').addEventListener('change', function() {
        const usaCajas = this.options[this.selectedIndex].getAttribute('data-usa-cajas') === '1';
        document.getElementById('stock_cajas_group').style.display = usaCajas ? 'block' : 'none';
        document.getElementById('unidades_por_caja_group').style.display = usaCajas ? 'block' : 'none';
        if (!usaCajas) {
            document.getElementById('stock_cajas').value = 0;
            document.getElementById('unidades_por_caja').value = 0;
        }
    });
</script>

<?php include 'includes/footer.php'; ?>