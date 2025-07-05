<?php
namespace App\Libraries;

class FormFields
{
    public function text($params)
    {
        $required = $params['required'] ?? false;
        $name = esc($params['name']);
        $label = esc($params['label']);

        $html = "<div class='col-md-6'>";
        $html .= "<label for='{$name}' class='form-label'>{$label}" . ($required ? " <span class='text-danger'>*</span>" : "") . "</label>";
        $html .= "<input type='text' class='form-control' id='{$name}' name='{$name}'" . ($required ? " required" : "") . ">";
        if ($required) {
            $html .= "<div class='invalid-feedback'>Por favor ingresa " . strtolower($label) . ".</div>";
        }
        $html .= "</div>";
        return $html;
    }

    public function tel($params)
    {
        $params['type'] = 'tel';
        return $this->text($params);
    }

    // Puedes agregar más funciones como textarea, checkboxGroup, radioGroup...
}