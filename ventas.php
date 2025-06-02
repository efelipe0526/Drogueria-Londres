<?php
// Check if the request expects a JSON response via Accept header or format parameter
$acceptHeader = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
$isJsonRequest = (strpos($acceptHeader, 'application/json') !== false) || (isset($_GET['format']) && $_GET['format'] === 'json');

if ($isJsonRequest) {
    header('Content-Type: application/json');
}

include 'db.php';
include 'functions.php';

$db = new Database();
$conexion = $db->getConnection();

$clientes = obtenerClientes();
$productos = obtenerProductos();
$formas_pago = $db->query("SELECT id, tipo, banco, cuenta FROM formas_pago");
$impuesto = $db->query("SELECT porcentaje FROM configuracion_impuestos WHERE id = 1")->fetch_assoc();

// Obtener el número de factura inicial desde la base de datos
$numero_factura_query = $db->query("SELECT numero_factura_inicial FROM configuracion_impuestos WHERE id = 1");
$numero_factura = $numero_factura_query->fetch_assoc()['numero_factura_inicial'] ?? '0000001';

// Asegurar que el número de factura tenga 7 dígitos
$numero_factura = str_pad($numero_factura, 7, '0', STR_PAD_LEFT);

// Obtener categorías
$categorias = $db->query("SELECT * FROM categorias");

// Obtener laboratorios
$laboratorios = $db->query("SELECT * FROM laboratorios");

// If it's a JSON request, return the initial data
if ($isJsonRequest) {
    $clientes_array = [];
    foreach ($clientes as $cliente) {
        $clientes_array[] = $cliente;
    }

    $productos_array = [];
    foreach ($productos as $producto) {
        $productos_array[] = $producto;
    }

    $formas_pago_array = [];
    while ($forma_pago = $formas_pago->fetch_assoc()) {
        $formas_pago_array[] = $forma_pago;
    }

    $categorias_array = [];
    while ($categoria = $categorias->fetch_assoc()) {
        $categorias_array[] = $categoria;
    }

    $laboratorios_array = [];
    while ($laboratorio = $laboratorios->fetch_assoc()) {
        $laboratorios_array[] = $laboratorio;
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'clientes' => $clientes_array,
            'productos' => $productos_array,
            'formas_pago' => $formas_pago_array,
            'impuesto' => $impuesto,
            'numero_factura' => $numero_factura,
            'categorias' => $categorias_array,
            'laboratorios' => $laboratorios_array
        ]
    ]);
    exit;
}
?>

<?php include 'includes/header.php'; ?>

<!-- Incluir SweetAlert2 desde la carpeta alerta -->
<script src="alerta/sweetalert2.all.min.js"></script>

<div class="container mt-5">
    <h1 class="text-center mb-4">Gestión de Ventas</h1>

    <!-- Botón para volver al menú principal -->
    <a href="index.php" class="btn btn-secondary mb-3">Volver al Menú Principal</a>

    <!-- Formulario para generar la factura -->
    <form id="formulario-venta" method="POST" action="procesar_venta.php" target="_blank">
        <!-- Selección del cliente -->
        <div class="form-group mb-2">
            <label for="cliente">Seleccionar Cliente:</label>
            <select class="form-control" id="cliente" name="cliente_id" required>
                <option value="">-- Seleccione un cliente --</option>
                <?php
                if (!empty($clientes)) {
                    foreach ($clientes as $cliente) {
                        echo "<option value='{$cliente['id']}'>{$cliente['nombre']}</option>";
                    }
                } else {
                    echo "<option disabled>No hay clientes disponibles</option>";
                }
                ?>
            </select>
        </div>

        <!-- Campo para escanear código de barras -->
        <div class="form-group mb-2">
            <label for="codigo_barras">Escanear Código de Barras:</label>
            <input type="text" class="form-control" id="codigo_barras" name="codigo_barras" placeholder="Escanear código de barras" autofocus>
        </div>

        <!-- Número de factura -->
        <div class="form-group">
            <label for="numero_factura">Número de factura:</label>
            <input type="text" class="form-control" id="numero_factura" name="numero_factura" value="<?php echo $numero_factura; ?>" readonly>
        </div>

        <!-- Subtotal -->
        <div class="form-group">
            <label for="subtotal">Subtotal:</label>
            <input type="number" class="form-control" id="subtotal" name="subtotal" readonly>
        </div>

        <!-- Impuesto -->
        <div class="form-group">
            <label for="impuesto">Impuesto (%):</label>
            <input type="number" class="form-control" id="impuesto" name="impuesto" value="<?php echo $impuesto['porcentaje']; ?>" readonly>
        </div>

        <!-- Total -->
        <div class="form-group">
            <label for="total">Total:</label>
            <input type="number" class="form-control" id="total" name="total" readonly required>
        </div>

        <!-- Selección de categoría -->
        <div class="form-group">
            <label for="categoria">Seleccionar Categoría:</label>
            <select class="form-control" id="categoria" name="categoria_id" required>
                <option value="">-- Seleccione una categoría --</option>
                <?php
                if (!empty($categorias)) {
                    while ($categoria = $categorias->fetch_assoc()) {
                        echo "<option value='{$categoria['id']}' data-usa-cajas='{$categoria['usa_cajas']}'>{$categoria['nombre']}</option>";
                    }
                } else {
                    echo "<option disabled>No hay categorías disponibles</option>";
                }
                ?>
            </select>
        </div>

        <!-- Selección de producto -->
        <div class="form-group">
            <label for="producto">Seleccionar Producto:</label>
            <select class="form-control" id="producto" name="producto">
                <option value="">-- Seleccione un producto --</option>
            </select>
        </div>

        <!-- Selección de laboratorio -->
        <div class="form-group">
            <label for="laboratorio">Seleccionar Laboratorio:</label>
            <select class="form-control" id="laboratorio" name="laboratorio">
                <option value="">-- Seleccione un laboratorio --</option>
                <?php
                if (!empty($laboratorios)) {
                    while ($laboratorio = $laboratorios->fetch_assoc()) {
                        echo "<option value='{$laboratorio['id']}'>{$laboratorio['nombre']}</option>";
                    }
                } else {
                    echo "<option disabled>No hay laboratorios disponibles</option>";
                }
                ?>
            </select>
        </div>

        <!-- Cantidad -->
        <div class="form-group">
            <label for="cantidad">Cantidad:</label>
            <input type="number" class="form-control" id="cantidad" name="cantidad" value="1" required>
        </div>

        <!-- Unidad -->
        <div class="form-group">
            <label for="unidad">Unidad:</label>
            <input type="text" class="form-control" id="unidad" name="unidad" readonly>
        </div>

        <!-- Precio por unidad -->
        <div class="form-group">
            <label for="precio_por_unidad">Precio por Unidad:</label>
            <input type="number" class="form-control" id="precio_por_unidad" name="precio_por_unidad" step="0.01">
        </div>

        <!-- Precio unitario (total) -->
        <div class="form-group">
            <label for="precio_unitario">Precio Unitario (Total):</label>
            <input type="number" class="form-control" id="precio_unitario" name="precio_unitario" readonly>
        </div>

        <!-- Checkboxes para seleccionar tipo de precio -->
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="usar_precio_venta" name="usar_precio_venta" checked>
            <label class="form-check-label" for="usar_precio_venta">Usar Precio Unitario (Total)</label>
        </div>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="usar_unidad" name="usar_unidad">
            <label class="form-check-label" for="usar_unidad">Usar Unidad y Precio por Unidad</label>
        </div>

        <!-- Botón para agregar producto -->
        <button type="button" class="btn btn-primary" id="agregarProducto">Agregar Producto</button>

        <!-- Lista de productos agregados -->
        <div id="productos-agregados" class="mt-3">
            <h4>Productos Agregados</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Laboratorio</th>
                        <th>Cantidad</th>
                        <th>Unidad (Total)</th>
                        <th>Precio por Unidad</th>
                        <th>Precio Unitario (Total)</th>
                        <th>Subtotal</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="lista-productos">
                    <!-- Aquí se agregarán los productos dinámicamente -->
                </tbody>
            </table>
        </div>

        <!-- Forma de pago -->
        <div class="form-group">
            <label for="forma_pago">Forma de Pago:</label>
            <select class="form-control" id="forma_pago" name="forma_pago" required>
                <option value="">-- Seleccione una forma de pago --</option>
                <?php
                if (!empty($formas_pago)) {
                    while ($forma_pago = $formas_pago->fetch_assoc()) {
                        $texto = $forma_pago['tipo'];
                        if ($forma_pago['tipo'] === 'transferencia' && !empty($forma_pago['banco']) && !empty($forma_pago['cuenta'])) {
                            $texto .= " ({$forma_pago['banco']} - {$forma_pago['cuenta']})";
                        }
                        echo "<option value='{$forma_pago['id']}' data-banco='{$forma_pago['banco']}' data-cuenta='{$forma_pago['cuenta']}'>$texto</option>";
                    }
                } else {
                    echo "<option disabled>No hay formas de pago disponibles</option>";
                }
                ?>
            </select>
        </div>

        <!-- Campos para banco y cuenta (solo se muestran si la forma de pago es transferencia) -->
        <div id="transferencia-group" style="display: none;">
            <div class="form-group">
                <label for="banco">Banco:</label>
                <input type="text" class="form-control" id="banco" name="banco" readonly>
            </div>
            <div class="form-group">
                <label for="cuenta">Cuenta:</label>
                <input type="text" class="form-control" id="cuenta" name="cuenta" readonly>
            </div>
        </div>

        <!-- División del pago -->
        <div class="form-group">
            <label for="dividir_pago">Dividir Pago:</label>
            <input type="checkbox" id="dividir_pago" name="dividir_pago">
        </div>

        <!-- Monto en efectivo -->
        <div class="form-group" id="efectivo-group" style="display: none;">
            <label for="monto_efectivo">Monto en Efectivo:</label>
            <input type="number" class="form-control" id="monto_efectivo" name="monto_efectivo">
        </div>

        <!-- Monto en otra forma de pago -->
        <div class="form-group" id="otra_forma_pago-group" style="display: none;">
            <label for="monto_otra_forma_pago">Monto en Otra Forma de Pago:</label>
            <input type="number" class="form-control" id="monto_otra_forma_pago" name="monto_otra_forma_pago">
        </div>

        <!-- Monto pagado -->
        <div class="form-group">
            <label for="monto_pagado">Monto Pagado:</label>
            <input type="number" class="form-control" id="monto_pagado" name="monto_pagado" required>
        </div>

        <!-- Vuelto -->
        <div class="form-group">
            <label for="vuelto">Vuelto:</label>
            <input type="number" class="form-control" id="vuelto" name="vuelto" readonly>
        </div>

        <!-- Checkbox para aplicar impuesto -->
        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="aplicar_impuesto" id="aplicar_impuesto">
            <label class="form-check-label" for="aplicar_impuesto">Aplicar Impuesto</label>
        </div>

        <!-- Botón para generar la factura -->
        <button type="submit" class="btn btn-success mt-3">Generar Factura</button>

        <!-- Botón para limpiar el formulario -->
        <button type="button" class="btn btn-secondary mt-3" id="limpiarFormulario">Limpiar Formulario</button>

        <!-- Botón para limpiar y generar una nueva factura -->
        <button type="button" class="btn btn-warning mt-3" id="limpiarYGenerarVenta">Limpiar y Generar Nueva Factura</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    let productosAgregados = [];

    // Actualizar el precio unitario, unidad, precio por unidad y laboratorio al seleccionar un producto
    document.getElementById('producto').addEventListener('change', function() {
        const productoSeleccionado = this.options[this.selectedIndex];
        const productoId = productoSeleccionado.value;
        const precio = parseFloat(productoSeleccionado.getAttribute('data-precio')) || 0;
        const unidadProducto = productoSeleccionado.getAttribute('data-unidad') || '';
        const precioPorUnidad = parseFloat(productoSeleccionado.getAttribute('data-precio-por-unidad')) || 0;
        const stockCajas = parseInt(productoSeleccionado.getAttribute('data-stock-cajas')) || 0;
        const unidadesPorCaja = parseInt(productoSeleccionado.getAttribute('data-unidades-por-caja')) || 1;
        const unidadesRestantes = parseInt(productoSeleccionado.getAttribute('data-unidades-restantes')) || 0;
        const usarPrecioVenta = document.getElementById('usar_precio_venta').checked;
        const cantidad = parseInt(document.getElementById('cantidad').value) || 1;

        // Validar precios al cargar el producto
        if (precio <= 0 && precioPorUnidad <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Precio inválido',
                text: `El producto "${productoSeleccionado.text}" no tiene un precio válido. Por favor, actualice el precio en la base de datos.`,
                confirmButtonText: 'Aceptar'
            });
            this.value = '';
            document.getElementById('precio_unitario').value = '';
            document.getElementById('precio_por_unidad').value = '';
            document.getElementById('unidad').value = '';
            document.getElementById('laboratorio').value = '';
            return;
        }

        // Determinar si la categoría usa cajas
        const categoriaSelect = document.getElementById('categoria');
        const categoriaSeleccionada = categoriaSelect.options[categoriaSelect.selectedIndex];
        const usaCajas = categoriaSeleccionada.getAttribute('data-usa-cajas') === '1';
        const usarUnidadCheckbox = document.getElementById('usar_unidad');
        const usarPrecioVentaCheckbox = document.getElementById('usar_precio_venta');

        // Si la categoría no usa cajas, forzar usar_precio_venta y deshabilitar usar_unidad
        if (!usaCajas) {
            usarUnidadCheckbox.checked = false;
            usarUnidadCheckbox.disabled = true;
            usarPrecioVentaCheckbox.checked = true;
            usarPrecioVentaCheckbox.disabled = true;
            document.getElementById('unidad').value = cantidad;
        } else {
            usarUnidadCheckbox.disabled = false;
            usarPrecioVentaCheckbox.disabled = false;
            document.getElementById('unidad').value = usarPrecioVenta ? unidadProducto || cantidad : cantidad;
        }

        // Actualizar precio unitario y precio por unidad
        document.getElementById('precio_unitario').value = precio.toFixed(2);
        document.getElementById('precio_por_unidad').value = precioPorUnidad.toFixed(2);

        // Cargar el laboratorio asociado al producto
        if (productoId) {
            fetch(`obtener_laboratorio.php?producto_id=${productoId}`)
                .then(response => response.text().then(text => JSON.parse(text)))
                .then(data => {
                    if (data.error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.error,
                            confirmButtonText: 'Aceptar'
                        });
                        document.getElementById('laboratorio').value = '';
                        return;
                    }
                    if (data.laboratorio_id && data.laboratorio_id !== '') {
                        document.getElementById('laboratorio').value = data.laboratorio_id;

                        // Verificar stock y fecha de vencimiento
                        verificarStockYVencimiento(productoId, data.laboratorio_id, productoSeleccionado.text, stockCajas, unidadesPorCaja, unidadesRestantes);
                    } else {
                        document.getElementById('laboratorio').value = '';
                        Swal.fire({
                            icon: 'warning',
                            title: 'Sin laboratorio',
                            text: 'No se encontró un laboratorio asociado a este producto.',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error al obtener laboratorio:', error);
                    document.getElementById('laboratorio').value = '';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: `No se pudo cargar el laboratorio del producto: ${error.message}`,
                        confirmButtonText: 'Aceptar'
                    });
                });
        } else {
            document.getElementById('laboratorio').value = '';
        }
    });

    // Vincular el campo Cantidad con Unidad solo cuando se usa Unidad y Precio por Unidad
    document.getElementById('cantidad').addEventListener('input', function() {
        const cantidad = this.value;
        const usarUnidad = document.getElementById('usar_unidad').checked;
        const unidadProducto = document.getElementById('producto').options[document.getElementById('producto').selectedIndex]?.getAttribute('data-unidad') || '';
        if (usarUnidad) {
            document.getElementById('unidad').value = cantidad;
        } else {
            document.getElementById('unidad').value = unidadProducto || cantidad;
        }
        const productoId = document.getElementById('producto').value;
        const laboratorioId = document.getElementById('laboratorio').value;
        if (productoId && laboratorioId) {
            const productoSeleccionado = document.getElementById('producto').options[document.getElementById('producto').selectedIndex];
            const stockCajas = parseInt(productoSeleccionado.getAttribute('data-stock-cajas')) || 0;
            const unidadesPorCaja = parseInt(productoSeleccionado.getAttribute('data-unidades-por-caja')) || 1;
            const unidadesRestantes = parseInt(productoSeleccionado.getAttribute('data-unidades-restantes')) || 0;
            verificarStockYVencimiento(productoId, laboratorioId, productoSeleccionado.text, stockCajas, unidadesPorCaja, unidadesRestantes);
        }
    });

    // Actualizar Unidad al cambiar las opciones de precio
    document.getElementById('usar_precio_venta').addEventListener('change', function() {
        const usarPrecioVenta = this.checked;
        const productoSeleccionado = document.getElementById('producto').options[document.getElementById('producto').selectedIndex];
        const unidadProducto = productoSeleccionado?.getAttribute('data-unidad') || '';
        const cantidad = document.getElementById('cantidad').value;

        document.getElementById('unidad').value = usarPrecioVenta ? unidadProducto || cantidad : cantidad;
        document.getElementById('usar_unidad').checked = !usarPrecioVenta;
    });

    document.getElementById('usar_unidad').addEventListener('change', function() {
        const usarUnidad = this.checked;
        const productoSeleccionado = document.getElementById('producto').options[document.getElementById('producto').selectedIndex];
        const unidadProducto = productoSeleccionado?.getAttribute('data-unidad') || '';
        const cantidad = document.getElementById('cantidad').value;

        document.getElementById('unidad').value = usarUnidad ? cantidad : unidadProducto || cantidad;
        document.getElementById('usar_precio_venta').checked = !usarUnidad;
    });

    // Agregar producto a la lista
    document.getElementById('agregarProducto').addEventListener('click', function() {
        const productoId = document.getElementById('producto').value;
        const laboratorioId = document.getElementById('laboratorio').value;
        const productoNombre = document.getElementById('producto').options[document.getElementById('producto').selectedIndex].text;
        const cantidad = parseFloat(document.getElementById('cantidad').value) || 1;
        const precioUnitario = parseFloat(document.getElementById('precio_unitario').value) || 0;
        const unidad = document.getElementById('unidad').value.trim() || '';
        const precioPorUnidad = parseFloat(document.getElementById('precio_por_unidad').value) || 0;
        const usarPrecioVenta = document.getElementById('usar_precio_venta').checked;
        const usarUnidadCheckbox = document.getElementById('usar_unidad').checked;

        // Determinar si la categoría usa cajas
        const categoriaSelect = document.getElementById('categoria');
        const categoriaSeleccionada = categoriaSelect.options[categoriaSelect.selectedIndex];
        const usaCajas = categoriaSeleccionada.getAttribute('data-usa-cajas') === '1';
        const usarUnidad = usaCajas && usarUnidadCheckbox;

        // Validar que al menos una opción de precio esté seleccionada
        if (!usarPrecioVenta && !usarUnidad) {
            Swal.fire({
                icon: 'warning',
                title: 'Selección requerida',
                text: 'Por favor, seleccione al menos una opción: Precio Unitario (Total) o Unidad y Precio por Unidad.',
                confirmButtonText: 'Aceptar'
            });
            return;
        }

        // Validar campos según las opciones seleccionadas
        if (usarPrecioVenta && (!precioUnitario || precioUnitario <= 0)) {
            Swal.fire({
                icon: 'warning',
                title: 'Campo requerido',
                text: 'Por favor, ingrese un Precio Unitario (Total) mayor a 0.',
                confirmButtonText: 'Aceptar'
            });
            return;
        }
        if (usarUnidad && (!precioPorUnidad || precioPorUnidad <= 0)) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos requeridos',
                text: 'Por favor, ingrese un Precio por Unidad mayor a 0.',
                confirmButtonText: 'Aceptar'
            });
            return;
        }

        if (productoId && laboratorioId && cantidad > 0) {
            // Verificar stock disponible
            fetch(`verificar_stock.php?producto_id=${productoId}&laboratorio_id=${laboratorioId}&cantidad=${cantidad}`)
                .then(response => response.text().then(text => JSON.parse(text)))
                .then(stockData => {
                    if (stockData.error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: stockData.error,
                            confirmButtonText: 'Aceptar'
                        });
                        return;
                    }
                    const stockCajas = stockData.stock_cajas;
                    const unidadesPorCaja = stockData.unidades_por_caja;
                    const unidadesRestantes = stockData.unidades_restantes;
                    const totalUnidades = (stockCajas * unidadesPorCaja) + unidadesRestantes;
                    if (totalUnidades < cantidad) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Stock insuficiente',
                            text: `No hay suficiente stock para el producto "${productoNombre}". Stock disponible: ${totalUnidades} unidades.`,
                            confirmButtonText: 'Aceptar'
                        });
                        return;
                    }

                    // Proceder a agregar el producto si hay stock suficiente
                    agregarProducto(productoId, laboratorioId, productoNombre, cantidad, precioUnitario, unidad, precioPorUnidad, usarPrecioVenta, usarUnidad);
                })
                .catch(error => {
                    console.error('Error al verificar stock:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: `No se pudo verificar el stock: ${error.message}`,
                        confirmButtonText: 'Aceptar'
                    });
                });
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Selección requerida',
                text: 'Por favor, seleccione un producto, un laboratorio y verifique la cantidad.',
                confirmButtonText: 'Aceptar'
            });
        }
    });

    // Función para agregar el producto a la lista
    function agregarProducto(productoId, laboratorioId, productoNombre, cantidad, precioUnitario, unidad, precioPorUnidad, usarPrecioVenta, usarUnidad) {
        // Calcular el subtotal según la opción seleccionada
        let subtotal = 0;
        if (usarUnidad) {
            subtotal = cantidad * precioPorUnidad;
        } else if (usarPrecioVenta) {
            subtotal = cantidad * precioUnitario;
        }

        // Agregar el producto a la lista
        const productoAgregado = {
            id: productoId,
            laboratorio_id: laboratorioId,
            nombre: productoNombre,
            cantidad: cantidad,
            unidad: unidad || '',
            precio_por_unidad: usarUnidad ? precioPorUnidad : '',
            precio_venta: usarPrecioVenta ? precioUnitario : '',
            subtotal: subtotal,
            usar_precio_venta: usarPrecioVenta,
            usar_unidad: usarUnidad
        };
        productosAgregados.push(productoAgregado);

        // Actualizar la tabla de productos agregados
        actualizarListaProductos();

        // Limpiar el formulario
        document.getElementById('producto').value = '';
        document.getElementById('laboratorio').value = '';
        document.getElementById('cantidad').value = 1;
        document.getElementById('unidad').value = '';
        document.getElementById('precio_por_unidad').value = '';
        document.getElementById('precio_unitario').value = '';
        document.getElementById('usar_precio_venta').checked = true;
        document.getElementById('usar_unidad').checked = false;
        document.getElementById('usar_unidad').disabled = false;
        document.getElementById('usar_precio_venta').disabled = false;
    }

    // Actualizar la lista de productos agregados
    function actualizarListaProductos() {
        const listaProductos = document.getElementById('lista-productos');
        listaProductos.innerHTML = '';

        let subtotalGeneral = 0;

        productosAgregados.forEach((producto, index) => {
            const laboratorioSelect = document.getElementById('laboratorio');
            const laboratorioOptions = Array.from(laboratorioSelect.options);
            const laboratorioNombre = laboratorioOptions.find(opt => opt.value === producto.laboratorio_id)?.text || 'Desconocido';
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${producto.nombre}</td>
                <td>${laboratorioNombre}</td>
                <td>${producto.cantidad}</td>
                <td>${producto.unidad || 'N/A'}</td>
                <td>${producto.usar_unidad ? '$' + parseFloat(producto.precio_por_unidad).toFixed(2) : 'N/A'}</td>
                <td>${producto.usar_precio_venta ? '$' + parseFloat(producto.precio_venta).toFixed(2) : 'N/A'}</td>
                <td>$${parseFloat(producto.subtotal).toFixed(2)}</td>
                <td>
                    <button type="button" class="btn btn-warning btn-sm" onclick="editarProducto(${index})">Editar</button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="eliminarProducto(${index})">Eliminar</button>
                </td>
            `;
            listaProductos.appendChild(row);
            subtotalGeneral += parseFloat(producto.subtotal);
        });

        // Actualizar el subtotal
        document.getElementById('subtotal').value = subtotalGeneral.toFixed(2);

        // Calcular el total (subtotal + impuesto)
        const impuesto = document.getElementById('aplicar_impuesto').checked ? subtotalGeneral * (parseFloat(document.getElementById('impuesto').value) / 100) : 0;
        const total = subtotalGeneral + impuesto;
        document.getElementById('total').value = total.toFixed(2);

        // Calcular el vuelto
        calcularVuelto();
    }

    // Escuchar cambios en el checkbox de impuesto
    document.getElementById('aplicar_impuesto').addEventListener('change', actualizarListaProductos);

    // Función para editar un producto
    function editarProducto(index) {
        const producto = productosAgregados[index];
        document.getElementById('producto').value = producto.id;
        document.getElementById('laboratorio').value = producto.laboratorio_id;
        document.getElementById('cantidad').value = producto.cantidad;
        document.getElementById('unidad').value = producto.unidad;
        document.getElementById('precio_por_unidad').value = producto.usar_unidad ? producto.precio_por_unidad : '';
        document.getElementById('precio_unitario').value = producto.usar_precio_venta ? producto.precio_venta : '';
        document.getElementById('usar_precio_venta').checked = producto.usar_precio_venta;
        document.getElementById('usar_unidad').checked = producto.usar_unidad;

        // Determinar si la categoría usa cajas para habilitar/deshabilitar checkboxes
        const categoriaSelect = document.getElementById('categoria');
        const categoriaSeleccionada = categoriaSelect.options[categoriaSelect.selectedIndex];
        const usaCajas = categoriaSeleccionada.getAttribute('data-usa-cajas') === '1';
        document.getElementById('usar_unidad').disabled = !usaCajas;
        document.getElementById('usar_precio_venta').disabled = !usaCajas;

        // Eliminar el producto de la lista
        productosAgregados.splice(index, 1);

        // Actualizar la lista de productos
        actualizarListaProductos();
    }

    // Función para eliminar un producto
    function eliminarProducto(index) {
        productosAgregados.splice(index, 1);
        actualizarListaProductos();
    }

    // Calcular el vuelto
    function calcularVuelto() {
        const total = parseFloat(document.getElementById('total').value) || 0;
        const montoPagado = parseFloat(document.getElementById('monto_pagado').value) || 0;
        const vuelto = montoPagado - total;
        document.getElementById('vuelto').value = vuelto.toFixed(2);
    }

    // Escuchar cambios en el monto pagado
    document.getElementById('monto_pagado').addEventListener('change', calcularVuelto);

    // Mostrar u ocultar campos de banco y cuenta según la forma de pago
    document.getElementById('forma_pago').addEventListener('change', function() {
        const formaPago = this.value;
        const transferenciaGroup = document.getElementById('transferencia-group');
        const banco = this.options[this.selectedIndex].getAttribute('data-banco');
        const cuenta = this.options[this.selectedIndex].getAttribute('data-cuenta');

        if (formaPago === 'transferencia') {
            transferenciaGroup.style.display = 'block';
            document.getElementById('banco').value = banco || '';
            document.getElementById('cuenta').value = cuenta || '';
        } else {
            transferenciaGroup.style.display = 'none';
            document.getElementById('banco').value = '';
            document.getElementById('cuenta').value = '';
        }
    });

    // Mostrar u ocultar campos de división de pago
    document.getElementById('dividir_pago').addEventListener('change', function() {
        const efectivoGroup = document.getElementById('efectivo-group');
        const otraFormaPagoGroup = document.getElementById('otra_forma_pago-group');

        if (this.checked) {
            efectivoGroup.style.display = 'block';
            otraFormaPagoGroup.style.display = 'block';
        } else {
            efectivoGroup.style.display = 'none';
            otraFormaPagoGroup.style.display = 'none';
            document.getElementById('monto_efectivo').value = '';
            document.getElementById('monto_otra_forma_pago').value = '';
        }
    });

    // Enviar los productos agregados al formulario
    document.getElementById('formulario-venta').addEventListener('submit', function(event) {
        event.preventDefault();

        const clienteId = document.getElementById('cliente').value;
        const numeroFactura = document.getElementById('numero_factura').value;
        const total = document.getElementById('total').value;
        const formaPago = document.getElementById('forma_pago').value;
        const montoPagado = document.getElementById('monto_pagado').value;
        const aplicarImpuesto = document.getElementById('aplicar_impuesto').checked ? 1 : 0;
        const dividirPago = document.getElementById('dividir_pago').checked;
        const montoEfectivo = dividirPago ? document.getElementById('monto_efectivo').value : 0;
        const montoOtraFormaPago = dividirPago ? document.getElementById('monto_otra_forma_pago').value : 0;

        // Validar que haya productos agregados
        if (productosAgregados.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Selección requerida',
                text: 'Por favor, agregue al menos un producto.',
                confirmButtonText: 'Aceptar'
            });
            return;
        }

        // Validar precios de los productos agregados
        for (let producto of productosAgregados) {
            if (producto.usar_precio_venta && (!producto.precio_venta || parseFloat(producto.precio_venta) <= 0)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Precio inválido',
                    text: `El producto "${producto.nombre}" tiene un Precio Unitario (Total) inválido. Por favor, edite el producto.`,
                    confirmButtonText: 'Aceptar'
                });
                return;
            }
            if (producto.usar_unidad && (!producto.precio_por_unidad || parseFloat(producto.precio_por_unidad) <= 0)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Precio inválido',
                    text: `El producto "${producto.nombre}" tiene un Precio por Unidad inválido. Por favor, edite el producto.`,
                    confirmButtonText: 'Aceptar'
                });
                return;
            }
        }

        // Validar que todos los campos obligatorios estén completos
        if (!clienteId || !numeroFactura || !total || !formaPago || !montoPagado) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos requeridos',
                text: 'Por favor, complete todos los campos obligatorios.',
                confirmButtonText: 'Aceptar'
            });
            return;
        }

        // Validar que los montos de división de pago sumen el total
        if (dividirPago && (parseFloat(montoEfectivo) + parseFloat(montoOtraFormaPago) !== parseFloat(total))) {
            Swal.fire({
                icon: 'warning',
                title: 'Error en división de pago',
                text: 'La suma de los montos en efectivo y otra forma de pago debe ser igual al total.',
                confirmButtonText: 'Aceptar'
            });
            return;
        }

        // Agregar los productos al formulario
        const productosInput = document.createElement('input');
        productosInput.type = 'hidden';
        productosInput.name = 'productos';
        productosInput.value = JSON.stringify(productosAgregados);
        this.appendChild(productosInput);

        // Agregar aplicar impuesto al formulario
        const aplicarImpuestoInput = document.createElement('input');
        aplicarImpuestoInput.type = 'hidden';
        aplicarImpuestoInput.name = 'aplicar_impuesto';
        aplicarImpuestoInput.value = aplicarImpuesto;
        this.appendChild(aplicarImpuestoInput);

        // Agregar división de pago al formulario
        const dividirPagoInput = document.createElement('input');
        dividirPagoInput.type = 'hidden';
        dividirPagoInput.name = 'dividir_pago';
        dividirPagoInput.value = dividirPago ? 1 : 0;
        this.appendChild(dividirPagoInput);

        const montoEfectivoInput = document.createElement('input');
        montoEfectivoInput.type = 'hidden';
        montoEfectivoInput.name = 'monto_efectivo';
        montoEfectivoInput.value = montoEfectivo;
        this.appendChild(montoEfectivoInput);

        const montoOtraFormaPagoInput = document.createElement('input');
        montoOtraFormaPagoInput.type = 'hidden';
        montoOtraFormaPagoInput.name = 'monto_otra_forma_pago';
        montoOtraFormaPagoInput.value = montoOtraFormaPago;
        this.appendChild(montoOtraFormaPagoInput);

        // Enviar el formulario
        this.submit();
    });

    // Limpiar el formulario
    document.getElementById('limpiarFormulario').addEventListener('click', function() {
        document.getElementById('formulario-venta').reset();
        productosAgregados = [];
        actualizarListaProductos();
        document.getElementById('subtotal').value = '';
        document.getElementById('total').value = '';
        document.getElementById('monto_pagado').value = '';
        document.getElementById('vuelto').value = '';
        document.getElementById('transferencia-group').style.display = 'none';
        document.getElementById('efectivo-group').style.display = 'none';
        document.getElementById('otra_forma_pago-group').style.display = 'none';
        document.getElementById('unidad').value = '';
        document.getElementById('usar_unidad').disabled = false;
        document.getElementById('usar_precio_venta').disabled = false;
    });

    // Limpiar y generar una nueva factura
    document.getElementById('limpiarYGenerarVenta').addEventListener('click', function() {
        fetch('obtener_numero_factura.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('formulario-venta').reset();
                productosAgregados = [];
                actualizarListaProductos();
                document.getElementById('subtotal').value = '';
                document.getElementById('total').value = '';
                document.getElementById('monto_pagado').value = '';
                document.getElementById('vuelto').value = '';
                document.getElementById('transferencia-group').style.display = 'none';
                document.getElementById('efectivo-group').style.display = 'none';
                document.getElementById('otra_forma_pago-group').style.display = 'none';
                document.getElementById('unidad').value = '';
                document.getElementById('usar_unidad').disabled = false;
                document.getElementById('usar_precio_venta').disabled = false;

                const numeroFactura = String(data.numero_factura).padStart(7, '0');
                document.getElementById('numero_factura').value = numeroFactura;
            })
            .catch(error => {
                console.error('Error al obtener el número de factura:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo obtener el número de factura.',
                    confirmButtonText: 'Aceptar'
                });
            });
    });

    // Cargar productos según la categoría seleccionada
    document.getElementById('categoria').addEventListener('change', function() {
        const categoriaId = this.value;
        const productoSelect = document.getElementById('producto');
        const usaCajas = this.options[this.selectedIndex].getAttribute('data-usa-cajas') === '1';
        const usarUnidadCheckbox = document.getElementById('usar_unidad');
        const usarPrecioVentaCheckbox = document.getElementById('usar_precio_venta');

        if (!usaCajas) {
            usarUnidadCheckbox.checked = false;
            usarUnidadCheckbox.disabled = true;
            usarPrecioVentaCheckbox.checked = true;
            usarPrecioVentaCheckbox.disabled = true;
        } else {
            usarUnidadCheckbox.disabled = false;
            usarPrecioVentaCheckbox.disabled = false;
        }

        productoSelect.innerHTML = '<option value="">-- Seleccione un producto --</option>';

        if (categoriaId) {
            fetch(`obtener_productos.php?categoria_id=${categoriaId}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status} - ${response.statusText}`);
                    }
                    return response.text();
                })
                .then(text => {
                    console.log('Respuesta cruda de obtener_productos.php:', text); // Depuración
                    const data = JSON.parse(text);
                    console.log('Datos parseados:', data); // Depuración
                    productoSelect.innerHTML = '<option value="">-- Seleccione un producto --</option>';
                    if (data.error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.error,
                            confirmButtonText: 'Aceptar'
                        });
                    } else if (Array.isArray(data) && data.length === 0) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Sin productos',
                            text: 'No hay productos disponibles para esta categoría.',
                            confirmButtonText: 'Aceptar'
                        });
                    } else if (Array.isArray(data)) {
                        data.forEach(producto => {
                            const precio = parseFloat(producto.precio) || 0;
                            const precioPorUnidad = parseFloat(producto.precio_por_unidad) || 0;
                            const unidadesPorCaja = parseInt(producto.unidades_por_caja) || 1;
                            if (precio <= 0 && precioPorUnidad <= 0) {
                                console.warn(`Producto "${producto.nombre}" omitido por precio inválido.`);
                            } else {
                                const unidad = usaCajas ? `${unidadesPorCaja} unidades/caja` : '';
                                const option = document.createElement('option');
                                option.value = producto.id;
                                option.setAttribute('data-precio', precio);
                                option.setAttribute('data-unidad', unidad);
                                option.setAttribute('data-precio-por-unidad', precioPorUnidad);
                                option.setAttribute('data-stock-cajas', producto.stock_cajas || 0);
                                option.setAttribute('data-unidades-por-caja', producto.unidades_por_caja || 1);
                                option.setAttribute('data-unidades-restantes', producto.unidades_restantes || 0);
                                option.textContent = producto.nombre;
                                productoSelect.appendChild(option);
                            }
                        });
                        // Intentar seleccionar el primer producto si hay opciones
                        if (productoSelect.options.length > 1) {
                            productoSelect.value = productoSelect.options[1].value; // Selecciona la primera opción válida
                            productoSelect.dispatchEvent(new Event('change')); // Dispara el evento para actualizar precios y stock
                        }
                    } else {
                        throw new Error('Respuesta no es un array válido');
                    }
                })
                .catch(error => {
                    console.error('Error al cargar productos:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: `No se pudieron cargar los productos. Verifique la conexión o contacte al administrador. Detalle: ${error.message}`,
                        confirmButtonText: 'Aceptar'
                    });
                });
        } else {
            usarUnidadCheckbox.disabled = false;
            usarPrecioVentaCheckbox.disabled = false;
        }
    });

    // Manejar escaneo de código de barras
    const codigoBarrasInput = document.getElementById('codigo_barras');
    const barcodePreview = document.createElement('svg');
    barcodePreview.id = 'barcode-preview';
    barcodePreview.style.display = 'none';
    document.body.appendChild(barcodePreview);

    codigoBarrasInput.addEventListener('input', function() {
        const codigo = this.value.trim();
        if (codigo) {
            $.ajax({
                url: 'buscar_producto.php',
                method: 'POST',
                data: {
                    codigo_barras: codigo
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Respuesta de buscar_producto.php:', response); // Depuración
                    if (response.error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error,
                            confirmButtonText: 'Aceptar'
                        });
                        codigoBarrasInput.value = '';
                        return;
                    }

                    const producto = response;
                    const precio = parseFloat(producto.precio) || 0;
                    const precioPorUnidad = parseFloat(producto.precio_por_unidad) || 0;
                    const usaCajas = producto.usa_cajas === '1';
                    const usarUnidadCheckbox = document.getElementById('usar_unidad');
                    const usarPrecioVentaCheckbox = document.getElementById('usar_precio_venta');

                    if (precio <= 0 && precioPorUnidad <= 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Precio inválido',
                            text: `El producto "${producto.nombre}" no tiene un precio válido. Por favor, actualice el precio en la base de datos.`,
                            confirmButtonText: 'Aceptar'
                        });
                        codigoBarrasInput.value = '';
                        return;
                    }

                    // Prellenar categoría
                    document.getElementById('categoria').value = producto.categoria_id;

                    // Prellenar producto
                    const productoSelect = document.getElementById('producto');
                    const unidad = usaCajas ? `${producto.unidades_por_caja || 1} unidades/caja` : '';
                    productoSelect.innerHTML = `<option value="${producto.id}" data-precio="${precio}" data-unidad="${unidad}" data-precio-por-unidad="${precioPorUnidad}" data-stock-cajas="${producto.stock_cajas}" data-unidades-por-caja="${producto.unidades_por_caja}" data-unidades-restantes="${producto.unidades_restantes}">${producto.nombre}</option>`;
                    productoSelect.value = producto.id;

                    // Prellenar laboratorio
                    document.getElementById('laboratorio').value = producto.laboratorio_id || '';

                    // Configurar checkboxes y precios
                    if (!usaCajas) {
                        usarUnidadCheckbox.checked = false;
                        usarUnidadCheckbox.disabled = true;
                        usarPrecioVentaCheckbox.checked = true;
                        usarPrecioVentaCheckbox.disabled = true;
                    } else {
                        usarUnidadCheckbox.disabled = false;
                        usarPrecioVentaCheckbox.disabled = false;
                        usarPrecioVentaCheckbox.checked = (precio > 0);
                        usarUnidadCheckbox.checked = (precioPorUnidad > 0 && precio <= 0);
                    }

                    document.getElementById('cantidad').value = 1;
                    document.getElementById('unidad').value = unidad || '';
                    document.getElementById('precio_unitario').value = precio.toFixed(2);
                    document.getElementById('precio_por_unidad').value = precioPorUnidad.toFixed(2);

                    // Disparar el evento change para verificar stock y fecha
                    productoSelect.dispatchEvent(new Event('change'));
                    codigoBarrasInput.value = '';

                    // Generar código de barras
                    JsBarcode(barcodePreview, codigo, {
                        format: "CODE128",
                        displayValue: true,
                        width: 2,
                        height: 40
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error AJAX:', xhr.responseText, status, error); // Depuración
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: `No se pudo procesar el código de barras. Verifique la conexión o contacte al administrador. Detalle: ${xhr.responseText || error}`,
                        confirmButtonText: 'Aceptar'
                    });
                    codigoBarrasInput.value = '';
                }
            });
        }
    });

    // Generar código de barras al cargar si hay valor
    document.addEventListener('DOMContentLoaded', function() {
        const initialCode = codigoBarrasInput.value.trim();
        if (initialCode) {
            JsBarcode(barcodePreview, initialCode, {
                format: "CODE128",
                displayValue: true,
                width: 2,
                height: 40
            });
        }
    });

    // Función para verificar stock y fecha de vencimiento
    function verificarStockYVencimiento(productoId, laboratorioId, productoNombre, stockCajas, unidadesPorCaja, unidadesRestantes) {
        fetch(`verificar_stock.php?producto_id=${productoId}&laboratorio_id=${laboratorioId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                console.log('Respuesta cruda de verificar_stock.php:', text);
                try {
                    const stockData = JSON.parse(text);
                    if (stockData.error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: stockData.error,
                            confirmButtonText: 'Aceptar'
                        });
                        return;
                    }

                    let messages = [];
                    let icon = 'warning';

                    // Verificar stock bajo
                    if (stockData.stock_cajas === undefined || stockData.unidades_por_caja === undefined || stockData.unidades_restantes === undefined) {
                        messages.push('Falta información de stock en la respuesta del servidor.');
                        icon = 'error';
                    } else {
                        const esPorUnidad = stockData.stock_cajas === 0 && stockData.unidades_por_caja === 0;

                        if (esPorUnidad) {
                            // Para productos por unidad (sin cajas) - Alerta solo si unidades_restantes <= 2
                            if (stockData.unidades_restantes <= 2) {
                                messages.push(`¡Stock Bajo! El producto "${productoNombre}" tiene ${stockData.unidades_restantes} unidades restantes.`);
                            }
                        } else {
                            // Para productos que se venden por caja
                            const totalUnidades = (stockData.stock_cajas * stockData.unidades_por_caja) + stockData.unidades_restantes;

                            // Alerta de Stock Bajo: menos de 2 cajas completas o menos de 30 unidades en total
                            if (stockData.stock_cajas < 2 || totalUnidades < 30) {
                                let mensajeStock = `¡Stock Bajo! El producto "${productoNombre}" tiene ${stockData.stock_cajas} caja(s) de ${stockData.unidades_por_caja} con ${stockData.unidades_restantes} unidades restantes.`;
                                messages.push(mensajeStock);
                            }

                            // Alerta de Stock Crítico: exactamente 1 caja
                            if (stockData.stock_cajas === 1 && stockData.unidades_por_caja > 0) {
                                messages.push(`¡Stock Crítico! El producto "${productoNombre}" tiene solo 1 caja disponible.`);
                            }
                        }
                    }

                    // Verificar fecha de vencimiento
                    if (stockData.fecha_vencimiento === undefined) {
                        messages.push('Falta la fecha de vencimiento en la respuesta del servidor.');
                        icon = 'error';
                    } else {
                        const fechaActual = new Date('2025-05-12'); // Fecha actual según tu contexto
                        const fechaVencimiento = new Date(stockData.fecha_vencimiento);
                        const unMesDespues = new Date(fechaActual);
                        unMesDespues.setMonth(unMesDespues.getMonth() + 1);

                        if (isNaN(fechaVencimiento.getTime())) {
                            messages.push(`La fecha de vencimiento del producto "${productoNombre}" es inválida.`);
                            icon = 'error';
                        } else if (fechaVencimiento < fechaActual) {
                            messages.push(`¡Producto Vencido! El producto "${productoNombre}" está vencido (Fecha de vencimiento: ${stockData.fecha_vencimiento}).`);
                            icon = 'error';
                        } else if (fechaVencimiento <= unMesDespues) {
                            const diferencia = Math.ceil((fechaVencimiento - fechaActual) / (1000 * 60 * 60 * 24));
                            messages.push(`¡Producto a Vencer! El producto "${productoNombre}" vence en ${diferencia} días (Fecha de vencimiento: ${stockData.fecha_vencimiento}).`);
                        }
                    }

                    if (messages.length > 0) {
                        Swal.fire({
                            title: messages.length > 1 ? "¡Advertencias Múltiples!" : messages[0].split('! ')[0] + '!',
                            html: messages.join('<br><br>'),
                            icon: icon,
                            confirmButtonText: 'Aceptar'
                        });
                    }
                } catch (e) {
                    console.error('Error al parsear JSON:', e, 'Respuesta cruda:', text);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: `No se pudo procesar la respuesta del servidor: ${e.message}. Respuesta: ${text}`,
                        confirmButtonText: 'Aceptar'
                    });
                }
            })
            .catch(error => {
                console.error('Error al verificar stock/vencimiento:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: `No se pudo verificar el stock o la fecha de vencimiento: ${error.message}`,
                    confirmButtonText: 'Aceptar'
                });
            });
    }
</script>

<?php include 'includes/footer.php'; ?>