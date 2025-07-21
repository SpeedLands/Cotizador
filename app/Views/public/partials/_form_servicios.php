<!-- app/Views/public/partials/_form_servicios.php -->
<h4 class="mt-4">2. Elige tus servicios</h4>

<div class="mb-3">
    <label for="cantidad_invitados" class="form-label fw-bold">Cantidad de Invitados</label>
    <input type="number" name="cantidad_invitados" id="cantidad_invitados" class="form-control" 
           value="<?= esc($cotizacion['cantidad_invitados'] ?? '1') ?>" min="1">
</div>

<div class="mb-3">
    <label class="form-label fw-bold">¿Qué Gustas en tu Cotización?</label>
    
    <?php foreach ($servicios as $servicio): ?>
        <div class="servicio-item" id="item-<?= $servicio['id'] ?>">
            <input class="form-check-input me-3 servicio-checkbox" 
                   type="checkbox" 
                   name="servicios[]" 
                   value="<?= $servicio['id'] ?>" 
                   id="servicio_<?= $servicio['id'] ?>"
                   data-min-personas="<?= $servicio['min_personas'] ?? 1 ?>"
                   data-tipo-cobro="<?= $servicio['tipo_cobro'] ?? 'fijo' ?>"
                   data-nombre-clave="<?= strtolower(str_replace(' ', '_', $servicio['nombre'])) ?>"
                   
                   <?php // --- LÓGICA PARA MARCAR EL CHECKBOX ---
                         // Comprueba si la variable con los IDs seleccionados existe Y si el ID del servicio actual está en ese array.
                         if (isset($servicios_seleccionados_ids) && in_array($servicio['id'], $servicios_seleccionados_ids)) {
                             echo 'checked';
                         }
                   ?>
            >
            
            <label class="form-check-label flex-grow-1" for="servicio_<?= $servicio['id'] ?>">
                <strong><?= htmlspecialchars($servicio['nombre']) ?></strong>
                <?php if (isset($servicio['min_personas']) && $servicio['min_personas'] > 1): ?>
                    <small class="d-block text-muted">Mínimo <?= $servicio['min_personas'] ?> invitados.</small>
                <?php endif; ?>
            </label>

            <?php if (isset($servicio['tipo_cobro']) && $servicio['tipo_cobro'] == 'por_litro'): ?>
                <?php
                    // Comprobamos si este servicio estaba seleccionado para decidir si mostramos el campo de litros
                    $isBebidaSelected = isset($servicios_seleccionados_ids) && in_array($servicio['id'], $servicios_seleccionados_ids);
                ?>
                <input type="number" name="litros_agua" class="form-control form-control-sm ms-2 <?= $isBebidaSelected ? '' : 'campo-oculto' ?>" 
                       style="width: 100px;" placeholder="Litros" min="1"
                       id="litros_servicio_<?= $servicio['id'] ?>"
                       value="<?= esc($cotizacion['litros_agua'] ?? '') ?>"> <!-- Suponiendo que guardas los litros -->
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <div class="mt-2">
         <label for="servicios_otros" class="form-label text-muted">Otros servicios que no estén en la lista:</label>
         <textarea name="servicios_otros" id="servicios_otros" class="form-control" rows="2"><?= esc($cotizacion['servicios_otros'] ?? '') ?></textarea>
    </div>
</div>