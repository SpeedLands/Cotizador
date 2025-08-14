<?php
// train_model.php

require __DIR__ . '/vendor/autoload.php';

use Phpml\Classification\KNearestNeighbors;
use Phpml\ModelManager;

echo "Iniciando entrenamiento del modelo de logística (v2)...\n";

// --- DATOS DE ENTRENAMIENTO ---
// Nuestro nuevo formato de vector es:
// [ feature_dificultad, feature_complejidad_texto, feature_requisitos_especiales ]

// Dificultad: facil=0, moderado=1, dificil=2
// Complejidad Texto: conteo de palabras clave (escaleras, lejos, etc.)
// Requisitos Especiales: conteo de palabras clave (alergia, vegano, etc.)

// --- Ejemplos ---
$samples = [
    // Casos Simples
    [0, 0, 0], // Dificultad fácil, sin texto complejo, sin requisitos especiales
    [0, 0, 0], // Otro caso simple

    // Casos Complejos por montaje
    [2, 0, 0], // Dificultad difícil, pero sin texto relevante
    [1, 1, 0], // Dificultad moderada y el texto menciona "hay que subir por escaleras"

    // Casos Complejos por requisitos especiales
    [0, 0, 1], // Dificultad fácil, pero el texto menciona "un invitado es celiaco"
    [0, 0, 2], // Dificultad fácil, pero piden "opciones para vegano y sin gluten" (2 palabras)

    // Casos Muy Complejos (combinación)
    [2, 1, 0], // Montaje difícil y además "el acceso está lejos"
    [1, 1, 2], // Montaje moderado, "acceso por elevador" y además "varios con alergia a la lactosa y uno vegano"
    [2, 2, 2]  // El peor caso: montaje difícil, "hay que usar generador y subir escaleras", y "menú especial para celiacos y veganos"
];

$labels = [
    'simple',
    'simple',
    'complejo',
    'complejo',
    'complejo',
    'muy_complejo', // Tener 2 requisitos especiales ya es muy complejo
    'muy_complejo',
    'muy_complejo',
    'muy_complejo'
];

// --- ENTRENAMIENTO ---
$classifier = new KNearestNeighbors($k = 3);
$classifier->train($samples, $labels);

echo "Modelo entrenado con éxito.\n";

// --- GUARDADO DEL MODELO ---
$modelPath = __DIR__ . '/writable/models/logistics_model.phpml';
if (!is_dir(dirname($modelPath))) {
    mkdir(dirname($modelPath), 0775, true);
}
$modelManager = new ModelManager();
$modelManager->saveToFile($classifier, $modelPath);

echo "Modelo guardado en: " . $modelPath . "\n";
echo "¡Entrenamiento completo!\n";