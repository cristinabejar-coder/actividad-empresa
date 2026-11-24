<?php
// Funciones auxiliares para el manejo de fotos y salida segura

const DIR_IMAGENES = __DIR__ . '/../imagenes/';
const FOTO_DEFECTO = 'sin_foto.svg';

function e($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

/**
 * Valida y guarda la foto subida. Devuelve el nombre guardado,
 * null si no se subió archivo, o lanza Exception si es inválida.
 */
function subirFoto(array $archivo): ?string {
    if (!isset($archivo['error']) || $archivo['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Error al subir la imagen (código ' . $archivo['error'] . ').');
    }
    if ($archivo['size'] > 2 * 1024 * 1024) {
        throw new Exception('La imagen no debe superar los 2 MB.');
    }
    $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
    if (!isset($permitidos[$mime])) {
        throw new Exception('Formato no permitido. Use JPG, PNG, GIF o WEBP.');
    }
    if (!is_dir(DIR_IMAGENES)) {
        mkdir(DIR_IMAGENES, 0755, true);
    }
    $nombre = time() . '_' . uniqid() . '.' . $permitidos[$mime];
    if (!move_uploaded_file($archivo['tmp_name'], DIR_IMAGENES . $nombre)) {
        throw new Exception('No se pudo guardar la imagen en el servidor.');
    }
    return $nombre;
}

/** Elimina físicamente la foto del servidor (no toca la foto por defecto). */
function borrarFoto(?string $nombre): void {
    if (!$nombre || $nombre === FOTO_DEFECTO) return;
    $ruta = DIR_IMAGENES . basename($nombre); // basename evita rutas maliciosas
    if (is_file($ruta)) {
        unlink($ruta);
    }
}

/** Ruta web de la foto (o la imagen por defecto si no existe). */
function urlFoto(?string $nombre): string {
    if ($nombre && is_file(DIR_IMAGENES . basename($nombre))) {
        return 'imagenes/' . rawurlencode(basename($nombre));
    }
    return 'imagenes/' . FOTO_DEFECTO;
}
