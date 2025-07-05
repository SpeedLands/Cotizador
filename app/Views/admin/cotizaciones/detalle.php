<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Cotización</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://uicdn.toast.com/editor/latest/toastui-editor.min.css" />
    <style>
        /* Ajuste opcional para que el editor no sea demasiado ancho por defecto si está en un container fluido */
        .toastui-editor-defaultUI {
            border: 1px solid #ced4da; /* Bootstrap-like border */
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h2>🧾 Detalle de Cotización #<?= isset($c['id']) ? esc($c['id']) : 'N/A' ?></h2>

    <a href="<?= base_url('admin/cotizaciones') ?>" class="btn btn-secondary mb-3">← Volver</a>

    <table class="table table-bordered">
        <tr><th>Cliente</th><td><?= isset($c['nombre_cliente']) ? esc($c['nombre_cliente']) : '' ?></td></tr>
        <tr><th>Teléfono</th><td><?= isset($c['telefono']) ? esc($c['telefono']) : '' ?></td></tr>
        <tr><th>Dirección</th><td><?= isset($c['direccion_evento']) ? esc($c['direccion_evento']) : '' ?></td></tr>
        <tr><th>Fecha del Evento</th><td><?= isset($c['fecha_evento']) ? esc($c['fecha_evento']) : '' ?></td></tr>
        <tr><th>Hora de Inicio</th><td><?= isset($c['hora_inicio']) ? esc($c['hora_inicio']) : '' ?></td></tr>
        <tr><th>Tipo de Evento</th><td><?= isset($c['tipo_evento']) ? esc($c['tipo_evento']) : '' ?></td></tr>
        <tr><th>Número de Invitados</th><td><?= isset($c['numero_invitados']) ? esc($c['numero_invitados']) : '' ?></td></tr>
        <tr><th>Rango de Presupuesto</th><td><?= isset($c['presupuesto_rango']) ? esc($c['presupuesto_rango']) : '' ?></td></tr>
        <tr><th>Presupuesto Estimado</th><td><strong>$<?= isset($c['presupuesto_total']) ? number_format($c['presupuesto_total'], 2) : '0.00' ?> MXN</strong></td></tr>
    </table>

    <h4>📌 Desglose Detallado</h4>

    <!-- Si quieres permitir la edición y guardado del desglose desde aquí -->
    <form id="formDesglose" method="POST" action="<?= base_url('admin/cotizaciones/guardar_desglose/' . (isset($c['id']) ? $c['id'] : '')) ?>">
        <div id="editor"></div> <!-- Contenedor para el editor Toast UI -->
        <input type="hidden" name="desglose_contenido" id="desglose_contenido_hidden">
        <br>
        <button type="submit" class="btn btn-primary mt-2">Guardar Cambios del Desglose</button>
    </form>

</div>

<script src="https://uicdn.toast.com/editor/latest/toastui-editor-all.min.js"></script>
<script>
    toastui.Editor.setLanguage('es_ES', {
        Markdown: 'Markdown',
        WYSIWYG: 'Visual',
        Write: 'Escribir',
        Preview: 'Vista previa',
        Headings: 'Encabezados',
        Paragraph: 'Párrafo',
        Bold: 'Negrita',
        Italic: 'Cursiva',
        Strike: 'Tachado',
        Code: 'Código en línea',
        Line: 'Línea divisoria',
        Blockquote: 'Cita',
        'Unordered list': 'Lista sin orden',
        'Ordered list': 'Lista numerada',
        Task: 'Lista de tareas',
        Indent: 'Aumentar sangría',
        Outdent: 'Reducir sangría',
        'Insert link': 'Insertar enlace',
        'Insert image': 'Insertar imagen',
        'Insert table': 'Insertar tabla',
        'Insert CodeBlock': 'Insertar bloque de código',
        'Upload Image': 'Subir imagen',
        URL: 'URL',
        Text: 'Texto',
        'Add row to up': 'Agregar fila arriba',
        'Add row to down': 'Agregar fila abajo',
        'Add column to left': 'Agregar columna a la izquierda',
        'Add column to right': 'Agregar columna a la derecha',
        'Remove row': 'Eliminar fila',
        'Remove column': 'Eliminar columna',
        'Align column to left': 'Alinear a la izquierda',
        'Align column to center': 'Alinear al centro',
        'Align column to right': 'Alinear a la derecha',
        'Remove table': 'Eliminar tabla',
        'Would you like to paste as table?': '¿Deseas pegar como tabla?',
        'No image': 'Sin imagen',
        'Image URL': 'URL de imagen',
        'Select image file': 'Seleccionar archivo de imagen',
        'Choose a file': 'Elegir archivo',
        'Markdown Guide': 'Guía Markdown',
        OK: 'Aceptar',
        Cancel: 'Cancelar',
        Close: 'Cerrar',
        'More': 'Más'
    });

    // Asegúrate de que $c['desglose'] existe y es una cadena
    const markdownCotizacion = <?= json_encode(isset($c['desglose']) ? $c['desglose'] : '*Empieza a escribir el desglose aquí...*') ?>;

    const editorElement = document.querySelector('#editor');
    if (editorElement) {
        const editor = new toastui.Editor({
            el: editorElement,
            height: '400px', // Ajusta la altura según necesites
            initialEditType: 'wysiwyg',
            previewStyle: 'vertical',
            language: 'es_ES',
            initialValue: markdownCotizacion
        });

        // Manejo del formulario del desglose
        const formDesglose = document.getElementById('formDesglose');
        if (formDesglose) {
            formDesglose.addEventListener('submit', function (event) {
                // event.preventDefault(); // Descomenta si quieres manejar el envío con AJAX
                const contenidoHTML = editor.getHTML(); // O getMarkdown() si prefieres guardar en Markdown
                document.getElementById('desglose_contenido_hidden').value = contenidoHTML;
                console.log("Contenido del desglose preparado para enviar:", contenidoHTML);
                // Si no usas AJAX, el formulario se enviará normalmente.
            });
        }
    } else {
        console.error("El elemento #editor no fue encontrado en el DOM.");
    }

</script>
</body>
</html>