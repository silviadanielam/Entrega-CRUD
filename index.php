<?php
// Archivo: C:\laragon\www\crud-ibero\index.php
require_once "conexion.php";

$mensaje = "";
$error = "";

// -------------------------------------------------------------------------
// 1. PROCESAR ACCIONES POST (CREAR, ACTUALIZAR O ELIMINAR)
// -------------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $accion = $_POST["accion"] ?? "";

  // A) ELIMINAR UN PRODUCTO (DELETE)
  if ($accion === "eliminar") {
    $idEliminar = intval($_POST["id"] ?? 0);
    if ($idEliminar > 0) {
      $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
      $stmt->execute([$idEliminar]);
      header("Location: index.php?ok=eliminado");
      exit;
    }
  }

  // SUMAR UNA UNIDAD AL STOCK: EJERCICIO 22
  if ($accion === "sumar_stock") {
    $id = intval($_POST["id"] ?? 0);

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE productos SET stock = stock + 1 WHERE id = ?");
        $stmt->execute([$id]);

        header("Location: index.php?ok=stock_aumentado");
        exit;
    }
  }

  // RESTAR UNA UNIDAD AL STOCK: EJERCICIO 22
  if ($accion === "restar_stock") {
      $id = intval($_POST["id"] ?? 0);

      if ($id > 0) {
          $stmt = $pdo->prepare("UPDATE productos SET stock = stock - 1 WHERE id = ? AND stock > 0");
          $stmt->execute([$id]);

          header("Location: index.php?ok=stock_disminuido");
          exit;
      }
  }

  // B) CREAR O ACTUALIZAR UN PRODUCTO (INSERT / UPDATE)
  if ($accion === "guardar") {
    $id = intval($_POST["id"] ?? 0);
    $nombre = trim($_POST["nombre"] ?? "");
    $marca = trim($_POST["marca"] ?? ""); //Ejercicio 23: agregué marca.
    $categoria = trim($_POST["categoria"] ?? "");
    $precio = $_POST["precio"] ?? "";
    $stock = $_POST["stock"] ?? "";

    if ($nombre === "" || $marca === "" || $categoria === "" || $precio === "" || $stock === "") { //Ejercicio 23: agregrué marca.
    $error = "Todos los campos son obligatorios.";

    } elseif (strlen($nombre) < 3) {
        $error = "El nombre debe tener al menos 3 caracteres."; /* Ejercicio 20 */

    } elseif (!is_numeric($precio) || !is_numeric($stock) || intval($stock) < 0) {
        $error = "El precio debe ser numérico y el stock debe ser un número mayor o igual a 0."; /* Ejercicio 20 */

    } elseif (floatval($precio) < 1000) {
        $error = "El precio debe ser mayor o igual a 1000 pesos."; /* Ejercicio 20 */

    } else {
      if ($id > 0) {
        // UPDATE: Actualizar producto existente
        $sql = "UPDATE productos SET nombre = ?, marca = ?, categoria = ?, precio = ?, stock = ? WHERE id = ?"; //Ejercicio 23: agregué marca.
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $marca, $categoria, floatval($precio), intval($stock), $id]);
        header("Location: index.php?ok=actualizado");
        exit;
      } else {
        // INSERT: Crear producto nuevo
        $sql = "INSERT INTO productos (nombre, marca, categoria, precio, stock) VALUES (?, ?, ?, ?, ?)"; //Ejercicio 23: agregué marca.
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $marca, $categoria, floatval($precio), intval($stock)]);
        header("Location: index.php?ok=creado");
        exit;
      }
    }
  }
}

// -------------------------------------------------------------------------
// 2. SI PULSARON "EDITAR", BUSCAR ESE PRODUCTO PARA LLENAR EL FORMULARIO
// -------------------------------------------------------------------------
$productoEditar = [
  "id" => 0,
  "nombre" => "",
  "marca" => "",
  "categoria" => "Accesorios",
  "precio" => "",
  "stock" => 0
];

if (isset($_GET["editar"])) {
  $idEditar = intval($_GET["editar"]);
  $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
  $stmt->execute([$idEditar]);
  $encontrado = $stmt->fetch();
  if ($encontrado) {
    $productoEditar = $encontrado;
  }
}

// -------------------------------------------------------------------------
// 3. LEER TODOS LOS PRODUCTOS (SELECT) + BUSCADOR OPCIONAL POR GET
// -------------------------------------------------------------------------
$busqueda = trim($_GET["q"] ?? "");
if ($busqueda !== "") {
  $stmt = $pdo->prepare("SELECT * FROM productos WHERE nombre LIKE ? OR categoria LIKE ? ORDER BY id DESC");
  $termino = "%" . $busqueda . "%";
  $stmt->execute([$termino, $termino]);
} else {
  $stmt = $pdo->query("SELECT * FROM productos ORDER BY id DESC");
}
$productos = $stmt->fetchAll();

// Calcular totales del inventario
$totalUnidades = 0;
$valorInventario = 0;
foreach ($productos as $p) {
  $totalUnidades += $p["stock"];
  $valorInventario += ($p["precio"] * $p["stock"]);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CRUD Inventario IberoTech — PHP & MySQL</title>
  <style>
    *{box-sizing:border-box}
    body{font-family:system-ui,-apple-system,sans-serif;background:#f4f6f9;color:#1a1a2e;margin:0;padding:24px}
    .contenedor{max-width:960px;margin:0 auto}
    header{background:linear-gradient(120deg,#0d2b4a,#1178be);color:#fff;padding:18px 22px;border-radius:10px;margin-bottom:20px}
    header h1{margin:0;font-size:1.35rem}
    .kpis{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px}
    .kpi{background:#fff;padding:14px;border-radius:8px;border:1px solid #dce1e7}
    .kpi small{color:#5c6470;display:block}
    .kpi strong{font-size:1.25rem}
    .panel{display:grid;grid-template-columns:320px 1fr;gap:20px;align-items:start}
    @media(max-width:780px){.panel,.kpis{grid-template-columns:1fr}}
    .caja{background:#fff;padding:18px;border-radius:10px;border:1px solid #dce1e7}
    .caja h2{margin-top:0;font-size:1.05rem}
    label{display:block;font-size:.85rem;font-weight:600;margin:10px 0 4px}
    input,select{width:100%;padding:8px 10px;border:1px solid #cbd2d9;border-radius:6px;font-size:.92rem}
    .btn{display:inline-block;padding:9px 14px;border:none;border-radius:6px;font-weight:600;cursor:pointer;text-decoration:none;font-size:.88rem}
    .btn-primario{background:#1178be;color:#fff;width:100%;margin-top:14px}
    .btn-secundario{background:#e4e7ec;color:#1a1a2e;display:block;text-align:center;margin-top:8px}
    .btn-editar{background:#eef6ff;color:#1178be;border:1px solid #b6d8f2;padding:4px 8px}
    .btn-borrar{background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;padding:4px 8px}
    .alerta{padding:10px 14px;border-radius:8px;margin-bottom:16px;font-weight:600}
    .alerta-ok{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
    .alerta-err{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
    table{width:100%;border-collapse:collapse;font-size:.88rem;margin-top:10px}
    th,td{border-bottom:1px solid #e4e7ec;padding:9px 8px;text-align:left}
    th{background:#f8fafc;font-weight:700}
    .badge{padding:2px 8px;border-radius:12px;font-size:.75rem;font-weight:700}
    .b-ok{background:#dcfce7;color:#166534}
    .b-out{background:#fee2e2;color:#991b1b}
    .b-low{background:#ffedd5;color:#c2410c} /* Se agrega el color naranja para el estado intermedio (Ejercicio 21) */
    .buscador{display:flex;gap:8px;margin-bottom:10px}
  </style>
</head>
<body>
<div class="contenedor">
  <header>
    <h1>Inventario IberoTech — Aplicación CRUD (PHP + MySQL)</h1>
  </header>

  <?php if (isset($_GET["ok"])): ?>
    <div class="alerta alerta-ok">
      Operación realizada con éxito: registro <?= htmlspecialchars($_GET["ok"]) ?>.
    </div>
  <?php endif; ?>

  <?php if ($error !== ""): ?>
    <div class="alerta alerta-err"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="kpis">
    <div class="kpi"><small>Productos listados</small><strong><?= count($productos) ?></strong></div>
    <div class="kpi"><small>Unidades en Stock</small><strong><?= $totalUnidades ?> uds</strong></div>
    <div class="kpi"><small>Valor del Inventario</small><strong>$<?= number_format($valorInventario, 0, ',', '.') ?></strong></div>
  </div>

  <div class="panel">
    <!-- FORMULARIO CREAR / EDITAR -->
    <div class="caja">
      <h2><?= $productoEditar["id"] > 0 ? "Editar Producto #" . $productoEditar["id"] : "Nuevo Producto" ?></h2>
      <form method="POST" action="index.php">
        <input type="hidden" name="accion" value="guardar">
        <input type="hidden" name="id" value="<?= intval($productoEditar["id"]) ?>">

        <label>Nombre del producto</label>
        <input type="text" name="nombre" required value="<?= htmlspecialchars($productoEditar["nombre"]) ?>">

        <label>Marca</label> <!-- Ejercicio 23: agregué marca. -->
        <input type="text" name="marca" maxlength="60" required value="<?= htmlspecialchars($productoEditar["marca"]) ?>">

        <label>Categoría</label>
        <select name="categoria">
          <?php
            $categorias = ["Computadores", "Monitores", "Accesorios", "Almacenamiento", "Audio", "Redes"]; /* Agrego dos categorías: Audio y Redes (Ejercicio 20) */
            foreach ($categorias as $cat):
              $sel = ($productoEditar["categoria"] === $cat) ? "selected" : "";
          ?>
            <option value="<?= $cat ?>" <?= $sel ?>><?= $cat ?></option>
          <?php endforeach; ?>
        </select>

        <label>Precio unitario ($ COP)</label>
        <input type="number" name="precio" min="0" step="100" required value="<?= htmlspecialchars($productoEditar["precio"]) ?>">

        <label>Stock (unidades)</label>
        <input type="number" name="stock" min="0" step="1" required value="<?= htmlspecialchars($productoEditar["stock"]) ?>">

        <button type="submit" class="btn btn-primario">
          <?= $productoEditar["id"] > 0 ? "Guardar Cambios" : "Agregar al Inventario" ?>
        </button>

        <?php if ($productoEditar["id"] > 0): ?>
          <a href="index.php" class="btn btn-secundario">Cancelar edición</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- TABLA DE PRODUCTOS Y BUSCADOR -->
    <div class="caja">
      <form method="GET" action="index.php" class="buscador">
        <input type="text" name="q" placeholder="Buscar por nombre o categoría..." value="<?= htmlspecialchars($busqueda) ?>">
        <button type="submit" class="btn" style="background:#0d2b4a;color:#fff">Buscar</button>
        <?php if ($busqueda !== ""): ?>
          <a href="index.php" class="btn" style="background:#e4e7ec;color:#1a1a2e">Limpiar</a>
        <?php endif; ?>
      </form>

      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Producto</th>
            <th>Marca</th> <!-- Ejercicio 23: agregué marca. -->
            <th>Categoría</th>
            <th>Precio</th>
            <th>Stock</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($productos) === 0): ?>
            <tr><td colspan="7">No se encontraron productos.</td></tr>
          <?php endif; ?>
          <?php foreach ($productos as $p): ?>
            <tr>
              <td>#<?= $p["id"] ?></td>
              <td><b><?= htmlspecialchars($p["nombre"]) ?></b></td>
              <td><?= htmlspecialchars($p["marca"]) ?></td> <!-- Ejercicio 23: agregué marca. -->
              <td><?= htmlspecialchars($p["categoria"]) ?></td>
              <td>$<?= number_format($p["precio"], 0, ',', '.') ?></td>
              <td>
                <?php if ($p["stock"] == 0): ?>
                  <span class="badge b-out">Agotado</span>

                <?php elseif ($p["stock"] <= 3): ?>
                  <span class="badge b-low">¡Últimas <?= $p["stock"] ?> uds!</span> <!-- Ejercicio 21 -->

                <?php else: ?>
                  <span class="badge b-ok"><?= $p["stock"] ?> disp.</span>

                <?php endif; ?>
              </td>
              <!-- BOTONES EDITAR, +1, -1 Y BORRAR: EJERCICIO 22 -->
              <td style="display:flex;gap:6px;">

                  <!-- BOTÓN EDITAR -->
                  <a href="index.php?editar=<?= $p["id"] ?>" class="btn btn-editar">Editar</a>

                  <!-- BOTÓN SUMAR STOCK -->
                  <form method="POST" action="index.php">
                      <input type="hidden" name="accion" value="sumar_stock">
                      <input type="hidden" name="id" value="<?= $p["id"] ?>">
                      <button type="submit" class="btn">+1</button>
                  </form>

                  <!-- BOTÓN RESTAR STOCK -->
                  <form method="POST" action="index.php">
                      <input type="hidden" name="accion" value="restar_stock">
                      <input type="hidden" name="id" value="<?= $p["id"] ?>">
                      <button type="submit" class="btn">-1</button>
                  </form>

                  <!-- BOTÓN BORRAR -->
                  <form method="POST" action="index.php" onsubmit="return confirm('¿Seguro que deseas eliminar este producto?');">
                      <input type="hidden" name="accion" value="eliminar">
                      <input type="hidden" name="id" value="<?= $p["id"] ?>">
                      <button type="submit" class="btn btn-borrar">Borrar</button>
                  </form>

              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>