<!-- app/Views/public/partials/_form_cliente_evento.php -->
<h4 class="mt-4">1. Cuéntanos sobre ti y tu evento</h4>

<div class="mb-3">
    <label for="nombre_completo" class="form-label">Nombre Completo</label>
    <input type="text" name="nombre_completo" id="nombre_completo" class="form-control" 
           value="<?= esc($cotizacion['nombre_completo'] ?? '') ?>" required>
</div>

<div class="mb-3">
    <label for="whatsapp" class="form-label">WhatsApp a donde enviaremos la cotización</label>
    <input type="tel" name="whatsapp" id="whatsapp" class="form-control" placeholder="Ej: 871 123 4567" 
           value="<?= esc($cotizacion['whatsapp'] ?? '') ?>" required>
</div>

<div class="mb-3">
    <label class="form-label">Tipo de Evento</label>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="tipo_evento" id="evento_social" value="Social"
               <?= (($cotizacion['tipo_evento'] ?? 'Social') == 'Social') ? 'checked' : '' ?>>
        <label class="form-check-label" for="evento_social">Evento Social</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="tipo_evento" id="evento_empresarial" value="Empresarial"
               <?= (($cotizacion['tipo_evento'] ?? '') == 'Empresarial') ? 'checked' : '' ?>>
        <label class="form-check-label" for="evento_empresarial">Evento Empresarial o Corporativo</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="tipo_evento" id="evento_otro" value="Otro"
               <?= (($cotizacion['tipo_evento'] ?? '') == 'Otro') ? 'checked' : '' ?>>
        <label class="form-check-label" for="evento_otro">Otro</label>
    </div>
</div>

<div class="mb-3 <?= (($cotizacion['tipo_evento'] ?? '') == 'Empresarial') ? '' : 'campo-oculto' ?>" id="campo_nombre_empresa">
    <label for="nombre_empresa" class="form-label">Nombre de la Empresa</label>
    <input type="text" name="nombre_empresa" id="nombre_empresa" class="form-control"
           value="<?= esc($cotizacion['nombre_empresa'] ?? '') ?>">
</div>

<div class="mb-3">
    <label for="direccion_evento" class="form-label">Dirección y Referencia del Lugar del Evento</label>
    <textarea name="direccion_evento" id="direccion_evento" class="form-control" rows="2"><?= esc($cotizacion['direccion_evento'] ?? '') ?></textarea>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="fecha_evento" class="form-label">Día del Evento</label>
        <input type="text" name="fecha_evento" id="fecha_evento" class="form-control" 
               placeholder="Selecciona una fecha..."
               value="<?= esc($cotizacion['fecha_evento'] ?? '') ?>" required>
        <div id="fecha-error-mensaje" class="text-danger mt-1" style="display: none;"></div>
    </div>
    <div class="col-md-6 mb-3">
        <label for="hora_evento" class="form-label">Hora que comienza el Evento</label>
        <input type="time" name="hora_evento" id="hora_evento" class="form-control" 
               value="<?= esc($cotizacion['hora_evento'] ?? '') ?>" required>
    </div>
</div>

<div class="mb-3">
    <label for="horario_consumo" class="form-label">Horario del consumo de alimentos o bebidas</label>
    <input type="text" name="horario_consumo" id="horario_consumo" class="form-control" placeholder="Ej: 3:00 PM, o de 2:00 PM a 4:00 PM"
           value="<?= esc($cotizacion['horario_consumo'] ?? '') ?>">
    <div class="form-text">
        Si el servicio es continuo o no tienes una hora fija, puedes especificarlo aquí.
    </div>
</div>