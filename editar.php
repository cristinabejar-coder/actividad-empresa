<?php
require_once 'conexion/conexion.php';
require_once 'conexion/funciones.php';

session_start();

// Cargar datos actuales según el ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: index.php'); exit; }

$stmt = $conexion->prepare("SELECT * FROM empleados WHERE ID = :id");
$stmt->execute([':id' => $id]);
$emp = $stmt->fetch();
if (!$emp) {
    $_SESSION['mensaje'] = ['tipo' => 'warning', 'texto' => 'El empleado no existe.'];
    header('Location: index.php');
    exit;
}

$error = null;
$fotoActual = $emp['Foto']; // foto guardada en BD antes de editar

// UPDATE: procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nueva = null;
    try {
        $nombre    = trim($_POST['nombre'] ?? '');
        $apellidoP = trim($_POST['apellidoP'] ?? '');
        $apellidoM = trim($_POST['apellidoM'] ?? '');
        $correo    = trim($_POST['correo'] ?? '');

        // Conservar lo escrito si hay error
        $emp['Nombre'] = $nombre; $emp['ApellidoP'] = $apellidoP;
        $emp['ApellidoM'] = $apellidoM; $emp['Correo'] = $correo;

        if ($nombre === '' || $apellidoP === '') {
            throw new Exception('Nombre y Apellido Paterno son obligatorios.');
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('El correo electrónico no es válido.');
        }

        $nueva = subirFoto($_FILES['foto'] ?? []); // null si no se eligió archivo

        $sql = "UPDATE empleados
                SET Nombre = :nombre, ApellidoP = :apellidoP, ApellidoM = :apellidoM,
                    Correo = :correo, Foto = :foto
                WHERE ID = :id";
        $upd = $conexion->prepare($sql);
        $upd->execute([
            ':nombre'    => $nombre,
            ':apellidoP' => $apellidoP,
            ':apellidoM' => $apellidoM !== '' ? $apellidoM : null,
            ':correo'    => $correo,
            ':foto'      => $nueva ?? $fotoActual,
            ':id'        => $id,
        ]);

        if ($nueva) { borrarFoto($fotoActual); } // reemplazo: eliminar la foto anterior

        $_SESSION['mensaje'] = ['tipo' => 'success', 'texto' => 'Empleado actualizado correctamente.'];
        header('Location: index.php');
        exit;
    } catch (PDOException $ex) {
        borrarFoto($nueva);
        $error = $ex->getCode() === '23000'
            ? 'Ya existe otro empleado con ese correo electrónico.'
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
    <title>Editar Empleado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">

<div class="container bg-white p-4 rounded shadow-sm" style="max-width: 500px;">
    <h3 class="mb-3">Editar Empleado #<?= (int)$id ?></h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= (int)$id ?>">
        <div class="mb-2">
            <label class="form-label">Nombre:</label>
            <input type="text" name="nombre" class="form-control" value="<?= e($emp['Nombre']) ?>" required>
        </div>
        <div class="mb-2">
            <label class="form-label">Apellido Paterno:</label>
            <input type="text" name="apellidoP" class="form-control" value="<?= e($emp['ApellidoP']) ?>" required>
        </div>
        <div class="mb-2">
            <label class="form-label">Apellido Materno:</label>
            <input type="text" name="apellidoM" class="form-control" value="<?= e($emp['ApellidoM']) ?>">
        </div>
        <div class="mb-2">
            <label class="form-label">Correo Electrónico:</label>
            <input type="email" name="correo" class="form-control" value="<?= e($emp['Correo']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label d-block">Foto actual:</label>
            <img src="<?= e(urlFoto($emp['Foto'])) ?>" alt="Foto actual" class="rounded border mb-2"
                 width="100" height="100" style="object-fit: cover;">
            <input type="file" name="foto" class="form-control" accept="image/*">
            <div class="form-text">Opcional: elija una imagen solo si desea reemplazar la actual.</div>
        </div>
        <button type="submit" class="btn btn-warning w-100">Actualizar Registro</button>
        <a href="index.php" class="btn btn-secondary w-100 mt-2">Cancelar</a>
    </form>
</div>

</body>
</html>
