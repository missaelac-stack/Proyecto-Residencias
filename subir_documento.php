<?php
session_start();
require_once 'config/conexion.php'; // Ajusta la ruta a tu archivo de conexión

//if (!isset($_SESSION['usuario_id'])) {
    die("Acceso denegado.");
//}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo'])) {
    $id_residente = $_SESSION['usuario_id'];
    $tipo = $_POST['tipo_documento'];
    $archivo = $_FILES['archivo'];

    // Carpetas aisladas por alumno: uploads/residente_X/
    $directorio = "uploads/residente_" . $id_residente . "/";
    if (!file_exists($directorio)) {
        mkdir($directorio, 0777, true);
    }

    $nombre_original = basename($archivo['name']);
    $nombre_final = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $nombre_original);
    $ruta_destino = $directorio . $nombre_final;

    if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
        // Se guarda vinculando el id del usuario activo y estatus 'Pendiente'
        $sql = "INSERT INTO documentos (usuario_id, nombre_archivo, ruta_archivo, tipo_documento, estatus) 
                VALUES (:usuario_id, :nombre, :ruta, :tipo, 'Pendiente')";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $id_residente,
            ':nombre'     => $nombre_original,
            ':ruta'       => $ruta_destino,
            ':tipo'       => $tipo
        ]);

        header("Location: mi_perfil.php?status=success");
        exit();
    } else {
        echo "Error al mover el archivo al servidor.";
    }
}
?>//