<a href="logout.php" class="bg-rose-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg">
    Cerrar Sesión
</a>

<?php
session_start();
require_once 'config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$id_residente = $_SESSION['usuario_id'];

// 1. Documentos generales (visibles para todos)
$sql_todos = "SELECT * FROM documentos WHERE usuario_id IS NULL ORDER BY id DESC";
$stmt_todos = $conexion->prepare($sql_todos);
$stmt_todos->execute();
$todos_los_documentos = $stmt_todos->fetchAll(PDO::FETCH_ASSOC);

// 2. Mis documentos personales subidos
$sql_mios = "SELECT * FROM documentos WHERE usuario_id = :id ORDER BY id DESC";
$stmt_mios = $conexion->prepare($sql_mios);
$stmt_mios->execute([':id' => $id_residente]);
$mis_documentos = $stmt_mios->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SigeRes - Mi Perfil</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 p-6">
    <div class="max-w-5xl mx-auto space-y-6">

        <!-- FORMULARIO DE SUBIDA -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <h2 class="text-sm font-bold text-slate-700 uppercase mb-4">Subir Mi Documento</h2>
            <form action="subir_documento.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Tipo de Documento:</label>
                    <select name="tipo_documento" required class="w-full p-2 border border-slate-200 rounded-lg text-sm">
                        <option value="Anteproyecto">Anteproyecto</option>
                        <option value="Carta de Aceptación">Carta de Aceptación</option>
                        <option value="Anexo XXIX">Anexo XXIX</option>
                        <option value="Anexo XXX">Anexo XXX</option>
                        <option value="Reporte Final">Reporte Final</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Seleccionar Archivo (PDF):</label>
                    <input type="file" name="archivo" accept=".pdf,.doc,.docx" required class="w-full text-xs text-slate-500">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-900 text-white font-bold py-2 rounded-lg text-xs hover:bg-blue-800">
                        Guardar en Mi Perfil
                    </button>
                </div>
            </form>
        </div>

        <!-- MIS DOCUMENTOS SUBIDOS Y SU ESTATUS DE REVISIÓN -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <h2 class="text-sm font-bold text-slate-700 uppercase mb-4">Mis Documentos Subidos</h2>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-400 uppercase text-xs border-b">
                        <th class="py-3 px-4">Documento</th>
                        <th class="py-3 px-4">Tipo</th>
                        <th class="py-3 px-4 text-center">Estatus Admin</th>
                        <th class="py-3 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <?php if (count($mis_documentos) > 0): ?>
                        <?php foreach ($mis_documentos as $doc): ?>
                            <tr>
                                <td class="py-3 px-4 font-bold text-slate-800"><?php echo htmlspecialchars($doc['nombre_archivo']); ?></td>
                                <td class="py-3 px-4 text-xs"><span class="bg-blue-50 text-blue-900 px-2 py-1 rounded font-bold"><?php echo htmlspecialchars($doc['tipo_documento']); ?></span></td>
                                <td class="py-3 px-4 text-center">
                                    <?php 
                                    $color = "bg-amber-100 text-amber-800";
                                    if ($doc['estatus'] === 'Aprobado') $color = "bg-emerald-100 text-emerald-800";
                                    if ($doc['estatus'] === 'Rechazado') $color = "bg-rose-100 text-rose-800";
                                    ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold <?php echo $color; ?>">
                                        <?php echo $doc['estatus'] ?? 'Pendiente'; ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <button type="button" onclick="verPDF('<?php echo htmlspecialchars($doc['ruta_archivo']); ?>')" class="text-xs bg-slate-100 text-slate-700 px-2.5 py-1 rounded-lg font-semibold">👁️ Vista Previa</button>
                                    <a href="<?php echo htmlspecialchars($doc['ruta_archivo']); ?>" download class="text-xs bg-blue-50 text-blue-900 px-2.5 py-1 rounded-lg font-semibold ml-2">📥 Descargar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="py-4 text-center text-slate-400">Aún no has subido ningún documento.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- MODAL VISTA PREVIA -->
    <div id="modalPDF" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-xl w-full max-w-4xl h-[85vh] p-4 flex flex-col relative">
            <button onclick="cerrarPDF()" class="absolute top-3 right-4 font-bold text-slate-500">❌ Cerrar</button>
            <h3 class="text-sm font-bold text-slate-800 mb-3">Vista Previa del Documento</h3>
            <iframe id="iframeVisor" src="" class="w-full flex-1 border border-slate-200 rounded-lg"></iframe>
        </div>
    </div>

    <script>
        function verPDF(ruta) {
            document.getElementById('iframeVisor').src = ruta;
            document.getElementById('modalPDF').classList.remove('hidden');
        }
        function cerrarPDF() {
            document.getElementById('iframeVisor').src = '';
            document.getElementById('modalPDF').classList.add('hidden');
        }
    </script>
</body>
</html>