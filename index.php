<?php
require_once 'conexion/conexion.php';
require_once 'conexion/funciones.php';

session_start();
$mensaje = $_SESSION['mensaje'] ?? null;
unset($_SESSION['mensaje']);

// READ: listado de empleados
$stmt = $conexion->prepare("SELECT * FROM empleados ORDER BY ID");
$stmt->execute();
$empleados = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Módulo de Empleados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">

<div class="container bg-white p-4 rounded shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Lista de Empleados</h2>
        <a href="agregar.php" class="btn btn-success">+ Nuevo Empleado</a>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-<?= e($mensaje['tipo']) ?> alert-dismissible fade show" role="alert">
            <?= e($mensaje['texto']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark text-center">
            <tr>
                <th>ID</th>
                <th>Nombre Completo</th>
                <th>Correo</th>
                <th>Foto</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($empleados as $emp): ?>
            <tr>
                <td class="text-center"><?= e($emp['ID']) ?></td>
                <td><?= e(trim($emp['Nombre'] . ' ' . $emp['ApellidoP'] . ' ' . $emp['ApellidoM'])) ?></td>
                <td><?= e($emp['Correo']) ?></td>
                <td class="text-center">
                    <img src="<?= e(urlFoto($emp['Foto'])) ?>" alt="Foto de <?= e($emp['Nombre']) ?>"
                         class="rounded-circle border" width="60" height="60" style="object-fit: cover;">
                </td>
                <td class="text-center">
                    <a href="editar.php?id=<?= (int)$emp['ID'] ?>" class="btn btn-warning btn-sm">Editar</a>
                    <form action="eliminar.php" method="POST" class="d-inline"
                          onsubmit="return confirm('¿Deseas eliminar este registro?');">
                        <input type="hidden" name="id" value="<?= (int)$emp['ID'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$empleados): ?>
            <tr><td colspan="5" class="text-center text-muted">No hay empleados registrados.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
