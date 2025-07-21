<!-- app/Views/public/partials/_form_detalles_finales.php -->
<h4 class="mt-4">3. Detalles Finales</h4>

<div class="mb-3">
    <label class="form-label">¿Gustan agregar mesa y mantel para el servicio?</label>
     <div class="form-check">
        <input class="form-check-input" type="radio" name="mesa_mantel" id="mesa_si" value="Si" 
               <?= (($cotizacion['mesa_mantel'] ?? 'Si') == 'Si') ? 'checked' : '' ?>>
        <label class="form-check-label" for="mesa_si">Sí</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="mesa_mantel" id="mesa_no" value="No"
               <?= (($cotizacion['mesa_mantel'] ?? '') == 'No') ? 'checked' : '' ?>>
        <label class="form-check-label" for="mesa_no">No, ya tenemos donde montar el servicio</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="mesa_mantel" id="mesa_otro" value="Otro"
               <?= (($cotizacion['mesa_mantel'] ?? '') == 'Otro') ? 'checked' : '' ?>>
        <label class="form-check-label" for="mesa_otro">Otro</label>
    </div>
    <div id="campo_mesa_mantel_otro" class="mt-2 <?= (($cotizacion['mesa_mantel'] ?? '') == 'Otro') ? '' : 'campo-oculto' ?>">
         <input type="text" name="mesa_mantel_otro" class="form-control" placeholder="Por favor, especifica (ej: solo mantel, mesa de bar, etc.)"
                value="<?= esc($cotizacion['mesa_mantel_otro'] ?? '') ?>">
    </div>
</div>

<div class="mb-3">
    <label class="form-label">¿Gusta que alguien esté sirviendo en el evento? (Costo Adicional)</label>
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="personal_servicio" id="servicio_si" value="Si"
               <?= (($cotizacion['personal_servicio'] ?? '') == 'Si') ? 'checked' : '' ?>>
        <label class="form-check-label" for="servicio_si">Sí</label>
    </div>
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="personal_servicio" id="servicio_no" value="No"
               <?= (($cotizacion['personal_servicio'] ?? 'No') == 'No') ? 'checked' : '' ?>>
        <label class="form-check-label" for="servicio_no">No</label>
    </div>
</div>

<!-- Ojo: El campo de enchufe de café debe ser visible si 'Estación de Cafe' está seleccionada.
     Esto es más complejo de manejar solo con PHP aquí, pero el JS lo mostrará si la opción está marcada. -->
<div class="mb-3 campo-oculto" id="campo_enchufe_cafe">
    <label class="form-label">Para el servicio de café, ¿hay acceso a enchufes cerca?</label>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="acceso_enchufe" id="enchufe_si" value="Si"
               <?= (($cotizacion['acceso_enchufe'] ?? '') == 'Si') ? 'checked' : '' ?>>
        <label class="form-check-label" for="enchufe_si">Sí, hay cerca</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="acceso_enchufe" id="enchufe_no" value="No"
               <?= (($cotizacion['acceso_enchufe'] ?? '') == 'No') ? 'checked' : '' ?>>
        <label class="form-check-label" for="enchufe_no">No hay cerca, se necesita extensión</label>
    </div>
</div>

<div class="mb-3">
    <label for="dificultad_montaje" class="form-label">Describir dificultad de montaje</label>
    <textarea name="dificultad_montaje" id="dificultad_montaje" class="form-control" rows="3" placeholder="Ej: Distancia a caminar..."><?= esc($cotizacion['dificultad_montaje'] ?? '') ?></textarea>
</div>

<div class="mb-3">
    <label class="form-label">¿Cómo supiste de nosotros?</label>
    <select name="como_supiste" id="como_supiste" class="form-select">
        <option value="Recomendacion" <?= (($cotizacion['como_supiste'] ?? '') == 'Recomendacion') ? 'selected' : '' ?>>Recomendación</option>
        <option value="Redes Sociales" <?= (($cotizacion['como_supiste'] ?? '') == 'Redes Sociales') ? 'selected' : '' ?>>Redes Sociales</option>
        <option value="Por el Restaurante" <?= (($cotizacion['como_supiste'] ?? '') == 'Por el Restaurante') ? 'selected' : '' ?>>Por el Restaurante</option>
        <option value="Otro" <?= (($cotizacion['como_supiste'] ?? '') == 'Otro') ? 'selected' : '' ?>>Otro</option>
    </select>
    <div id="como_supiste_otro_wrapper" class="mt-2 <?= (($cotizacion['como_supiste'] ?? '') == 'Otro') ? '' : 'campo-oculto' ?>">
        <input type="text" name="como_supiste_otro" id="como_supiste_otro" class="form-control" placeholder="Por favor, especifica"
               value="<?= esc($cotizacion['como_supiste_otro'] ?? '') ?>">
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Tipo de consumidores</label>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="tipo_consumidores" id="consumidores_hombres" value="Hombres"
               <?= (($cotizacion['tipo_consumidores'] ?? '') == 'Hombres') ? 'checked' : '' ?>>
        <label class="form-check-label" for="consumidores_hombres">Hombres</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="tipo_consumidores" id="consumidores_mujeres" value="Mujeres"
               <?= (($cotizacion['tipo_consumidores'] ?? '') == 'Mujeres') ? 'checked' : '' ?>>
        <label class="form-check-label" for="consumidores_mujeres">Mujeres</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="tipo_consumidores" id="consumidores_ninos" value="Niños"
               <?= (($cotizacion['tipo_consumidores'] ?? '') == 'Niños') ? 'checked' : '' ?>>
        <label class="form-check-label" for="consumidores_ninos">Niños</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="tipo_consumidores" id="consumidores_mixto" value="Mixto"
               <?= (($cotizacion['tipo_consumidores'] ?? 'Mixto') == 'Mixto') ? 'checked' : '' ?>>
        <label class="form-check-label" for="consumidores_mixto">Mixto</label>
    </div>
</div>

<div class="mb-3">
    <label for="restricciones" class="form-label">¿Alguna restricción alimenticia?</label>
    <textarea name="restricciones" id="restricciones" class="form-control" rows="2" placeholder="Ej: Alergias a nueces..."><?= esc($cotizacion['restricciones'] ?? '') ?></textarea>
</div>

<div class="mb-3">
    <label for="requisitos_adicionales" class="form-label">¿Requisitos Adicionales o especiales?</label>
    <textarea name="requisitos_adicionales" id="requisitos_adicionales" class="form-control" rows="3" placeholder="Ej: Necesitamos montaje silencioso, acceso especial para un proveedor, etc."><?= esc($cotizacion['requisitos_adicionales'] ?? '') ?></textarea>
</div>

 <div class="mb-3">
    <label for="presupuesto" class="form-label">¿Rango de presupuesto en mente? (Opcional)</label>
    <input type="text" name="presupuesto" id="presupuesto" class="form-control" placeholder="Ej: $5,000 - $7,000 MXN"
           value="<?= esc($cotizacion['presupuesto'] ?? '') ?>">
</div>