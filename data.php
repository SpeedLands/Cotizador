<?php

// ======================================================================
// CONFIGURACIÓN - ¡MODIFICA ESTOS VALORES SI SON DIFERENTES!
// ======================================================================
$db_host = 'pahoran.com.mx'; // o 127.0.0.1
$db_user = 'raulpaho_juan';      // Tu usuario de la base de datos
$db_pass = 'JDDidgs1!';          // Tu contraseña
$db_name = 'raulpaho_juanMapolato'; // El nombre de la base de datos

// ======================================================================
// LÓGICA PARA OBTENER Y MOSTRAR DATOS
// ======================================================================

// Verificamos si se ha especificado una tabla en la URL
if (!isset($_GET['tabla'])) {
    die("
        <h1>Uso del Script</h1>
        <p>Por favor, especifica la tabla que quieres ver en la URL.</p>
        <p><strong>Ejemplos:</strong></p>
        <ul>
            <li>Para ver todos los registros de la tabla 'usuarios': <a href='?tabla=usuarios'>?tabla=usuarios</a></li>
            <li>Para ver el último registro de la tabla 'cotizaciones': <a href='?tabla=cotizaciones&modo=ultimo'>?tabla=cotizaciones&modo=ultimo</a></li>
        </ul>
    ");
}

// Conexión a la base de datos
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Comprobar la conexión
if ($mysqli->connect_error) {
    die("ERROR DE CONEXIÓN: " . $mysqli->connect_error);
}

// Establecer el juego de caracteres a UTF-8 para evitar problemas con acentos y ñ
$mysqli->set_charset('utf8');

$nombre_tabla = $_GET['tabla'];
$modo = isset($_GET['modo']) ? $_GET['modo'] : 'todos'; // 'todos' o 'ultimo'

// --- Medida de seguridad básica para evitar inyección SQL en el nombre de la tabla ---
// Escapamos el nombre de la tabla para mayor seguridad
$tabla_segura = '`' . $mysqli->real_escape_string($nombre_tabla) . '`';

// Construimos la consulta SQL según el modo
$sql = "";
$titulo = "";

if ($modo === 'ultimo') {
    // Para obtener el "último" registro, asumimos que tienes una columna ID autoincremental.
    // Si tu columna se llama diferente, cámbiala aquí (ej. 'id_cotizacion').
    $columna_id = 'id'; 
    $sql = "SELECT * FROM $tabla_segura ORDER BY `$columna_id` DESC LIMIT 1";
    $titulo = "Mostrando el último registro de la tabla: " . htmlspecialchars($nombre_tabla);
} else {
    // Modo por defecto: obtener todos los registros
    $sql = "SELECT * FROM $tabla_segura";
    $titulo = "Mostrando TODOS los registros de la tabla: " . htmlspecialchars($nombre_tabla);
}

$resultado = $mysqli->query($sql);

// Comprobamos si la consulta falló (por ejemplo, si la tabla no existe)
if (!$resultado) {
    die("<h1>Error en la consulta</h1><p>No se pudo ejecutar la consulta. Revisa si el nombre de la tabla '<strong>" . htmlspecialchars($nombre_tabla) . "</strong>' es correcto.</p><p>Error de MySQL: " . $mysqli->error . "</p>");
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor de Datos de la Base de Datos</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        h1 { color: #333; }
        .no-data { color: #888; font-style: italic; }
    </style>
</head>
<body>

    <h1><?php echo $titulo; ?></h1>

    <?php if ($resultado->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <?php
                    // Imprimir los encabezados de la tabla (nombres de las columnas)
                    $campos = $resultado->fetch_fields();
                    foreach ($campos as $campo) {
                        echo "<th>" . htmlspecialchars($campo->name) . "</th>";
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                // Imprimir cada fila de datos
                while ($fila = $resultado->fetch_assoc()) {
                    echo "<tr>";
                    foreach ($fila as $valor) {
                        // Usamos htmlspecialchars para evitar problemas si los datos contienen HTML (Seguridad XSS)
                        echo "<td>" . htmlspecialchars($valor) . "</td>";
                    }
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-data">La tabla '<?php echo htmlspecialchars($nombre_tabla); ?>' no tiene registros o no existe.</p>
    <?php endif; ?>

</body>
</html>

<?php
// Liberar resultado y cerrar conexión
$resultado->free();
$mysqli->close();
?>