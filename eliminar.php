<?php
require_once 'conexion/conexion.php';
require_once 'conexion/funciones.php';

session_start();

// DELETE: solo por POST (evita borrados accidentales vía enlaces/rastreadores)
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
    try {
        // 1) Obtener el nombre de la foto antes de borrar el registro
        $q = $conexion->prepare("SELECT Foto FROM empleados WHERE ID = :id");
        $q->execute([':id' => $id]);
        $foto = $q->fetchColumn();

        // 2) Eliminar el registro
        $stmt = $conexion->prepare("DELETE FROM empleados WHERE ID = :id");
        $stmt->execute([':id' => $id]);

        // 3) Eliminar físicamente la foto del servidor
        if ($stmt->rowCount() > 0) {
            borrarFoto($foto ?: null);
            $_SESSION['mensaje'] = ['tipo' => 'success', 'texto' => 'Empleado eliminado correctamente.'];
        }
    } catch (PDOException $ex) {
        $_SESSION['mensaje'] = ['tipo' => 'danger', 'texto' => 'No se pudo eliminar: ' . $ex->getMessage()];
    }
}

header('Location: index.php');
exit;
