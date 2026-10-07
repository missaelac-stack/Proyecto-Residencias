<?php
//session_start();

// 1. Verificar sesión iniciada
//if (!isset($_SESSION['usuario_id'])) {
  //  header("Location: index.php");
    //exit();
//}

// 2. INCLUIR LA CONEXIÓN
require_once 'config/conexion.php';
// Consulta para obtener todos los documentos subidos por los residentes
$sql_admin_docs = "SELECT d.id, d.nombre_archivo, d.ruta_archivo, d.tipo_documento, d.fecha_subida, d.estatus, u.correo 
                FROM documentos d 
                INNER JOIN usuarios u ON d.usuario_id = u.id 
                ORDER BY d.id DESC";
$stmt_admin_docs = $conexion->prepare($sql_admin_docs);
$stmt_admin_docs->execute();

// CORRECCIÓN: Usar la sintaxis nativa de MySQLi en lugar de PDO
$resultado = $stmt_admin_docs->get_result();
$documentos_alumnos = $resultado->fetch_all(MYSQLI_ASSOC);
?>

<!-- TABLA DE REVISIÓN EN EL PANEL ADMIN -->
<div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm mt-6">
    <h3 class="font-bold text-slate-800 text-base mb-4">Revisión de Documentos de Residentes</h3>
    
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50 text-slate-400 uppercase text-xs border-b">
                <th class="py-3 px-4">Residente</th>
                <th class="py-3 px-4">Documento</th>
                <th class="py-3 px-4 text-center">Estatus Actual</th>
                <th class="py-3 px-4 text-right">Acciones de Evaluación</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm">
            <?php if (!empty($documentos_alumnos)): ?>
                <?php foreach ($documentos_alumnos as $doc): ?>
                    <tr>
                        <td class="py-3 px-4 font-semibold text-slate-700"><?php echo htmlspecialchars($doc['correo']); ?></td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-800"><?php echo htmlspecialchars($doc['nombre_archivo']); ?></div>
                            <span class="text-xs bg-blue-50 text-blue-900 px-2 py-0.5 rounded font-medium"><?php echo htmlspecialchars($doc['tipo_documento']); ?></span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <?php 
                            $badge = "bg-amber-100 text-amber-800";
                            if ($doc['estatus'] === 'Aprobado') $badge = "bg-emerald-100 text-emerald-800";
                            if ($doc['estatus'] === 'Rechazado') $badge = "bg-rose-100 text-rose-800";
                            ?>
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold <?php echo $badge; ?>">
                                <?php echo htmlspecialchars($doc['estatus']); ?>
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right space-x-1">
                            <button type="button" onclick="verPDF('<?php echo htmlspecialchars($doc['ruta_archivo']); ?>')" class="text-xs bg-slate-100 text-slate-700 px-2.5 py-1 rounded hover:bg-slate-200">
                                👁️ Ver
                            </button>
                            <a href="cambiar_estatus_doc.php?id=<?php echo $doc['id']; ?>&estado=Aprobado" class="text-xs bg-emerald-600 text-white px-2.5 py-1 rounded font-bold hover:bg-emerald-700">
                                ✓ Aprobar
                            </a>
                            <a href="cambiar_estatus_doc.php?id=<?php echo $doc['id']; ?>&estado=Rechazado" class="text-xs bg-rose-600 text-white px-2.5 py-1 rounded font-bold hover:bg-rose-700" onclick="return confirm('¿Seguro que deseas marcar este documento como Rechazado?');">
                                ✕ Rechazar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="py-4 text-center text-slate-500">No hay documentos registrados por revisar.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>