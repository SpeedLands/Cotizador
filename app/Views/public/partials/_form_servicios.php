<!-- app/Views/public/partials/_form_servicios.php -->
<h4 class="mt-4">2. Elige tus servicios</h4>

<div class="mb-3">
    <label for="cantidad_invitados" class="form-label fw-bold">Cantidad de Invitados</label>
    <input type="number" name="cantidad_invitados" id="cantidad_invitados" class="form-control" 
           value="<?= esc($cotizacion['cantidad_invitados'] ?? '1') ?>" min="1">
</div>

<div class="mb-3">
    <label class="form-label fw-bold">¿Qué Gustas en tu Cotización?</label>
    
    <!-- SECCIÓN DE SERVICIOS REGULARES (CHECKBOXES) -->
    <?php foreach ($servicios as $servicio): ?>
        <?php if (strpos($servicio['nombre'], 'Modalidad:') === false): // Si NO es una modalidad ?>
            <div class="servicio-item" id="item-<?= $servicio['id'] ?>">
                <input class="form-check-input me-3 servicio-checkbox" 
                       type="checkbox" 
                       name="servicios[]" 
                       value="<?= $servicio['id'] ?>" 
                       id="servicio_<?= $servicio['id'] ?>"
                       data-min-personas="<?= $servicio['min_personas'] ?? 1 ?>"
                       data-tipo-cobro="<?= $servicio['tipo_cobro'] ?? 'fijo' ?>"
                       data-precio-base="<?= $servicio['precio_base'] ?>"
                       data-nombre-clave="<?= strtolower(str_replace(' ', '_', $servicio['nombre'])) ?>"
                       <?= (isset($servicios_seleccionados_ids) && in_array($servicio['id'], $servicios_seleccionados_ids)) ? 'checked' : '' ?>
                >
                <label class="form-check-label flex-grow-1" for="servicio_<?= $servicio['id'] ?>">
                    <strong><?= htmlspecialchars($servicio['nombre']) ?></strong>
                    <?php if (isset($servicio['min_personas']) && $servicio['min_personas'] > 1): ?>
                        <small class="d-block text-muted">Mínimo <?= $servicio['min_personas'] ?> invitados.</small>
                    <?php endif; ?>
                </label>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <!-- SECCIÓN DE MODALIDADES (RADIO BUTTONS) -->
    <label class="form-label fw-bold mt-4">Modalidad del Servicio</label>
    <?php foreach ($servicios as $servicio): ?>
        <?php if (strpos($servicio['nombre'], 'Modalidad:') !== false): // Si SÍ es una modalidad ?>
            <div class="servicio-item" id="item-<?= $servicio['id'] ?>">
                <input class="form-check-input me-3 modalidad-radio" 
                       type="radio" 
                       name="servicios[]"  
                       value="<?= $servicio['id'] ?>" 
                       id="servicio_<?= $servicio['id'] ?>"
                       data-tipo-cobro="fijo"
                       data-precio-base="<?= $servicio['precio_base'] ?>"
                       
                       <?php // --- LÓGICA PARA MARCAR POR DEFECTO ---
                             // Si el precio es 0, lo marcamos por defecto.
                             if ($servicio['precio_base'] == 0) { echo 'checked'; }
                       ?>
                >
                <label class="form-check-label flex-grow-1" for="servicio_<?= $servicio['id'] ?>">
                    <!-- Quitamos el prefijo "Modalidad:" para que se vea más limpio -->
                    <strong><?= htmlspecialchars(str_replace('Modalidad: ', '', $servicio['nombre'])) ?></strong>
                    <?php if ($servicio['precio_base'] > 0): ?>
                        <small class="d-block text-success">(Costo adicional)</small>
                    <?php else: ?>
                        <small class="d-block text-muted">(Opción estándar)</small>
                    <?php endif; ?>
                </label>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="mt-2">
         <label for="servicios_otros" class="form-label text-muted">Otros servicios que no estén en la lista:</label>
         <textarea name="servicios_otros" id="servicios_otros" class="form-control" rows="2"><?= esc($cotizacion['servicios_otros'] ?? '') ?></textarea>
    </div>
</div>