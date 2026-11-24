<?php
require_once 'conexion/conexion.php';
require_once 'conexion/funciones.php';

session_start();
$error = null;
$datos = ['nombre' => '', 'apellidoP' => '', 'apellidoM' => '', 'correo' => ''];

// CREATE: procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'nombre'    => trim($_POST['nombre'] ?? ''),
        'apellidoP' => trim($_POST['apellidoP'] ?? ''),
        'apellidoM' => trim($_POST['apellidoM'] ?? ''),
        'correo'    => trim($_POST['correo'] ?? ''),
    ];
    $foto = null;
    try {
        if ($datos['nombre'] === '' || $datos['apellidoP'] === '') {
            throw new Exception('Nombre y Apellido Paterno son obligatorios.');
        }
        if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('El correo electrónico no es válido.');
        }
        $foto = subirFoto($_FILES['foto'] ?? []) ?? FOTO_DEFECTO;

        $sql = "INSERT INTO empleados (Nombre, ApellidoP, ApellidoM, Correo, Foto)
                VALUES (:nombre, :apellidoP, :apellidoM, :correo, :foto)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':nombre'    => $datos['nombre'],
            ':apellidoP' => $datos['apellidoP'],
            ':apellidoM' => $datos['apellidoM'] !== '' ? $datos['apellidoM'] : null,
            ':correo'    => $datos['correo'],
            ':foto'      => $foto,
        ]);
        $_SESSION['mensaje'] = ['tipo' => 'success', 'texto' => 'Empleado registrado correctamente.'];
        header('Location: index.php');
        exit;
    } catch (PDOException $ex) {
        borrarFoto($foto); // no dejar archivos huérfanos si falla el INSERT
        $error = $ex->getCode() === '23000'
            ? 'Ya existe un empleado con ese correo electrónico.'
            : 'Error de base de datos: ' . $ex->getMessage();
    } catch (Exception $ex) {
        $error = $ex->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agregar Empleado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">

<div class="container bg-white p-4 rounded shadow-sm" style="max-width: 500px;">
    <h3 class="mb-3">Nuevo Empleado</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="mb-2">
            <label class="form-label">Nombre:</label>
            <input type="text" name="nombre" class="form-control" value="<?= e($datos['nombre']) ?>" required>
        </div>
        <div class="mb-2">
            <label class="form-label">Apellido Paterno:</label>
            <input type="text" name="apellidoP" class="form-control" value="<?= e($datos['apellidoP']) ?>" required>
        </div>
        <div class="mb-2">
            <label class="form-label">Apellido Materno:</label>
            <input type="text" name="apellidoM" class="form-control" value="<?= e($datos['apellidoM']) ?>">
        </div>
        <div class="mb-2">
            <label class="form-label">Correo Electrónico:</label>
            <input type="email" name="correo" class="form-control" value="<?= e($datos['correo']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Foto de perfil (JPG, PNG, GIF o WEBP, máx. 2 MB):</label>
            <input type="file" name="foto" class="form-control" accept="image/*">
        </div>
        <button type="submit" class="btn btn-primary w-100">Guardar Empleado</button>
        <a href="index.php" class="btn btn-secondary w-100 mt-2">Cancelar</a>
    </form>
</div>

</body>
</html>
