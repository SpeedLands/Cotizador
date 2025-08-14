<?php
// app/Libraries/LogisticsAIService.php

namespace App\Libraries;

use Phpml\Classification\KNearestNeighbors;
use Phpml\ModelManager;

class LogisticsAIService
{
    private $model;
    private $modelPath;

    public function __construct()
    {
        $this->modelPath = WRITEPATH . 'models/logistics_model.phpml';
        if (file_exists($this->modelPath)) {
            $modelManager = new ModelManager();
            $this->model = $modelManager->restoreFromFile($this->modelPath);
        }
    }

    /**
     * Preprocesa los datos de entrada a un formato numérico.
     * Analiza texto libre para detectar palabras clave que implican complejidad.
     */
    private function preprocess(array $data): array
    {
        // Feature 1: Dificultad de montaje (categórica a numérica)
        $dificultadMap = ['facil' => 0, 'moderado' => 1, 'dificil' => 2];
        $dificultadValue = strtolower($data['dificultad_montaje'] ?? 'facil');
        $feature_dificultad = $dificultadMap[$dificultadValue] ?? 0;

        // Concatenamos los campos de texto libre para analizarlos juntos
        $textoCompleto = strtolower(
            ($data['servicios_otros'] ?? '') . ' ' . 
            ($data['requisitos_adicionales'] ?? '')
        );

        // Feature 2: Conteo de palabras clave de complejidad
        $palabrasClaveComplejidad = ['escaleras', 'elevador', 'lejos', 'distancia', 'piso alto', 'sin acceso', 'generador'];
        $feature_complejidad_texto = 0;
        foreach ($palabrasClaveComplejidad as $palabra) {
            if (strpos($textoCompleto, $palabra) !== false) {
                $feature_complejidad_texto++; // Aumenta por cada palabra clave encontrada
            }
        }
        
        // Feature 3: Conteo de palabras clave de requisitos especiales
        $palabrasClaveRequisitos = ['alergia', 'vegano', 'vegetariano', 'gluten', 'celiaco', 'lactosa', 'sin azucar'];
        $feature_requisitos_especiales = 0;
        foreach ($palabrasClaveRequisitos as $palabra) {
            if (strpos($textoCompleto, $palabra) !== false) {
                $feature_requisitos_especiales++;
            }
        }

        // El "Vector de Características" que el modelo usará.
        // El orden DEBE ser el mismo que en el entrenamiento.
        return [
            $feature_dificultad,
            $feature_complejidad_texto,
            $feature_requisitos_especiales
        ];
    }
    
    /**
     * Predice el costo y justificación basados en los datos de la cotización.
     */
    public function predict(array $quoteData): array
    {
        if (!$this->model) {
            return [
                'costo' => 0,
                'justificacion' => 'Modelo de IA no entrenado. No se aplicaron cargos de logística/especiales.'
            ];
        }

        $sample = $this->preprocess($quoteData);
        $predictedCategory = $this->model->predict($sample);

        // Mapear la categoría predicha a un resultado
        switch ($predictedCategory) {
            case 'muy_complejo':
                return [
                    'costo' => 750.00,
                    'justificacion' => 'Costo adicional por alta complejidad logística (ej: montaje difícil, acceso complicado) y/o requisitos especiales que requieren insumos o preparación adicional.'
                ];
            case 'complejo':
                return [
                    'costo' => 400.00,
                    'justificacion' => 'Costo adicional por logística compleja (ej: dificultad de montaje, servicios no listados) o por requisitos alimenticios específicos.'
                ];
            case 'simple':
            default:
                return [
                    'costo' => 0,
                    'justificacion' => 'La logística y requisitos del evento se consideran estándar. No se aplican cargos adicionales.'
                ];
        }
    }
}