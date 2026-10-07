/*session_start();
require_once 'config/conexion.php'; // Ajusta según la ubicación de tu conexión

// Verificar permisos de Administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    die("Acceso no autorizado.");
}

if (isset($_GET['id']) && isset($_GET['estado'])) {
    $doc_id = intval($_GET['id']);
    $nuevo_estado = $_GET['estado'];

    if (in_array($nuevo_estado, ['Aprobado', 'Rechazado', 'Pendiente'])) {
        $sql = "UPDATE documentos SET estatus = :estado WHERE id = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':estado' => $nuevo_estado,
            ':id'     => $doc_id
        ]);
    }
}

header("Location: panel_admin.php?status=actualizado");
exit();
?>