<?php
session_start();
require_once __DIR__ . '/db.php';

// Simular usuario logueado para prueba
if (!isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = 'Admin';
}

$msg = '';
$showForm = false;

// Si se hace clic en "Agregar nuevo producto"
if (isset($_GET['add'])) {
    $showForm = true;
}

// Si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $size = $_POST['size'] ?? '';
    $batch_date = $_POST['batch_date'] ?? '';
    $expiry_date = $_POST['expiry_date'] ?? '';
    $maker = $_POST['maker'] ?? '';

    if ($name && $size && $batch_date && $expiry_date && $maker) {
        $stmt = $pdo->prepare("INSERT INTO products (name, size, batch_date, expiry_date, maker) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $size, $batch_date, $expiry_date, $maker]);
        $msg = "✅ Producto agregado correctamente";
    } else {
        $msg = "⚠️ Por favor llena todos los campos";
        $showForm = true; // volver a mostrar formulario si hay error
    }
}

// Buscar productos
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$params = [];
$sql = 'SELECT * FROM products';
if ($search) {
    $sql .= ' WHERE name LIKE ? OR maker LIKE ?';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$sql .= ' ORDER BY created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard - Herbolaria</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="#">Herbolaria</a>
    <div class="d-flex">
        <span class="me-3">Hola, <?=htmlspecialchars($_SESSION['user_name'])?></span>
        <a href="logout.php" class="btn btn-sm btn-outline-danger">Salir</a>
    </div>
  </div>
</nav>
<div class="container py-4">

  <!-- Mensaje -->
  <?php if ($msg): ?>
    <div id="alert-msg" class="alert alert-success text-center"><?=$msg?></div>
  <?php endif; ?>

  <!-- Mostrar formulario si se pidió -->
  <?php if ($showForm): ?>
    <div class="card mb-4 shadow-sm">
      <div class="card-header bg-success text-white">Agregar nuevo producto</div>
      <div class="card-body">
        <form method="post" action="dashboard.php">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nombre</label>
              <input type="text" name="name" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Tamaño</label>
              <select name="size" class="form-control" required>
                <option value="">Seleccionar...</option>
                <option>Grande</option>
                <option>Chico</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Fecha de elaboración</label>
              <input type="date" name="batch_date" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Fecha de caducidad</label>
              <input type="date" name="expiry_date" class="form-control" required>
            </div>
            <div class="col-md-12">
              <label class="form-label">Hecho por</label>
              <input type="text" name="maker" class="form-control" required>
            </div>
          </div>
          <div class="mt-3 text-end">
            <button type="submit" class="btn btn-success">Agregar Producto</button>
            <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
          </div>
        </form>
      </div>
    </div>
  <?php else: ?>
    <!-- Botón para mostrar formulario -->
    <div class="mb-3 text-end">
      <a href="dashboard.php?add=1" class="btn btn-primary">➕ Agregar nuevo producto</a>
    </div>
  <?php endif; ?>

  <!-- Buscador -->
  <div class="row mb-3">
    <div class="col-md-8">
      <form class="d-flex" method="get">
        <input class="form-control me-2" name="q" placeholder="Buscar por nombre o fabricante" value="<?=htmlspecialchars($search)?>">
        <button class="btn btn-outline-success">Buscar</button>
      </form>
    </div>
  </div>

  <!-- Lista de productos -->
  <div class="row">
    <?php if(empty($products)): ?>
      <div class="col-12"><div class="alert alert-info">No hay productos registrados.</div></div>
    <?php endif; ?>

    <?php foreach($products as $p): ?>
      <div class="col-md-6 col-lg-4 mb-4">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <h5 class="card-title"><?=htmlspecialchars($p['name'])?></h5>
            <p class="card-text small">Tamaño: <strong><?=htmlspecialchars($p['size'])?></strong></p>
            <p class="card-text small">Lote: <?=htmlspecialchars($p['batch_date'])?> — Caduca: <?=htmlspecialchars($p['expiry_date'])?></p>
            <p class="card-text small">Hecho por: <?=htmlspecialchars($p['maker'])?></p>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Script para ocultar mensaje después de 5 segundos -->
<script>
  setTimeout(() => {
    const alertBox = document.getElementById("alert-msg");
    if (alertBox) {
      alertBox.style.transition = "opacity 1s";
      alertBox.style.opacity = "0";
      setTimeout(() => alertBox.remove(), 1000);
    }
  }, 2000);
</script>

</body>
</html>
