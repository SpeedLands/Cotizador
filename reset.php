<?php

// ======================================================================
// CONFIGURACIÓN - ¡MODIFICA ESTOS VALORES!
// ======================================================================
$db_host = 'pahoran.com.mx'; // o 127.0.0.1
$db_user = 'raulpaho_juan';      // Tu usuario de la base de datos
$db_pass = 'JDDidgs1!';          // Tu contraseña
$db_name = 'raulpaho_juanMapolato'; // El nombre de la base de datos a resetear
$sql_file_path = 'mapolato.sql'; // Ruta al archivo SQL para importar

// ======================================================================
// MEDIDA DE SEGURIDAD PARA EVITAR EJECUCIÓN ACCIDENTAL
// Para ejecutar, debes acceder a: reset_database.php?confirm=YES
// ======================================================================
if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'YES') {
    die("
        <h1>Confirmación Requerida</h1>
        <p>Este script borrará TODAS las tablas de la base de datos '<strong>" . htmlspecialchars($db_name) . "</strong>' y las reemplazará con el contenido de '<strong>" . htmlspecialchars($sql_file_path) . "</strong>'.</p>
        <p>Esta acción es irreversible.</p>
        <p>Si estás seguro de que quieres continuar, haz clic en el siguiente enlace:</p>
        <a href='?confirm=YES' style='font-size: 18px; color: red; border: 1px solid red; padding: 10px;'>SÍ, ESTOY SEGURO - RESETEAR LA BASE DE DATOS</a>
    ");
}

// ======================================================================
// INICIO DEL PROCESO DE RESETEO
// ======================================================================

header('Content-Type: text/plain; charset=utf-8');
echo "INICIANDO PROCESO DE RESETEO DE LA BASE DE DATOS...\n\n";

// Conexión a la base de datos
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Comprobar la conexión
if ($mysqli->connect_error) {
    die("ERROR DE CONEXIÓN: " . $mysqli->connect_error . "\n");
}
echo "✔️ Conexión a la base de datos '" . $db_name . "' establecida.\n";

try {
    // --- PASO 1: BORRAR TODAS LAS TABLAS EXISTENTES ---
    echo "\n--- PASO 1: BORRANDO TABLAS ANTIGUAS ---\n";
    
    // Desactivar temporalmente la comprobación de claves foráneas para evitar errores al borrar
    $mysqli->query('SET FOREIGN_KEY_CHECKS = 0');

    // Obtener todas las tablas de la base de datos
    $result = $mysqli->query('SHOW TABLES');
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_array()) {
            $tableName = $row[0];
            echo "Borrando tabla: " . $tableName . "... ";
            if ($mysqli->query('DROP TABLE `' . $tableName . '`')) {
                echo "OK\n";
            } else {
                throw new Exception("Error al borrar la tabla " . $tableName . ": " . $mysqli->error);
            }
        }
    } else {
        echo "No se encontraron tablas para borrar.\n";
    }

    // Reactivar la comprobación de claves foráneas
    $mysqli->query('SET FOREIGN_KEY_CHECKS = 1');
    echo "✔️ Todas las tablas antiguas han sido borradas.\n";

    // --- PASO 2: IMPORTAR EL ARCHIVO SQL ---
    echo "\n--- PASO 2: IMPORTANDO NUEVA ESTRUCTURA DESDE '" . $sql_file_path . "' ---\n";

    // Comprobar si el archivo SQL existe y es legible
    if (!is_readable($sql_file_path)) {
        throw new Exception("Error: No se pudo leer el archivo SQL en '" . $sql_file_path . "'.");
    }

    // Leer el contenido del archivo SQL
    $sql_commands = file_get_contents($sql_file_path);

    // Ejecutar las sentencias SQL (multi_query es necesario para archivos con múltiples sentencias)
    if ($mysqli->multi_query($sql_commands)) {
        // Es necesario limpiar los resultados de multi_query
        do {
            if ($result = $mysqli->store_result()) {
                $result->free();
            }
        } while ($mysqli->next_result());
        echo "✔️ Archivo SQL importado correctamente.\n";
    } else {
        throw new Exception("Error al importar el archivo SQL: " . $mysqli->error);
    }

} catch (Exception $e) {
    // Si algo sale mal, mostrar el error y reactivar las claves foráneas
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    $mysqli->query('SET FOREIGN_KEY_CHECKS = 1');
    
} finally {
    // Cerrar la conexión en cualquier caso
    $mysqli->close();
    echo "\nPROCESO FINALIZADO. Conexión cerrada.\n";
}

?>