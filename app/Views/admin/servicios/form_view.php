<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<?php
    // Para simplificar la lógica en la vista, definimos una variable booleana
    $isEditing = isset($servicio) && $servicio;
    // Capturamos el servicio de validación para un acceso más fácil
    $validation = \Config\Services::validation();
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <!-- MEJORA: El formulario está contenido en una tarjeta para un mejor diseño -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h1 class="h4 mb-0"><?= esc($titulo) ?></h1>
                <p class="text-muted small mb-0"><?= $isEditing ? 'Modifica los datos del servicio.' : 'Completa el formulario para añadir un nuevo servicio.' ?></p>
            </div>
            <div class="card-body">
                <!-- El action del form es dinámico -->
                <form action="<?= $isEditing ? site_url('admin/servicios/actualizar') : site_url('admin/servicios/guardar') ?>" method="post">
                    <?= csrf_field() ?>

                    <?php if ($isEditing): ?>
                        <input type="hidden" name="id" value="<?= esc($servicio['id']) ?>">
                    <?php endif; ?>

                    <!-- Campo Nombre -->
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del Servicio</label>
                        <!-- MEJORA: El input se marca como inválido si hay un error de validación -->
                        <input type="text" name="nombre" id="nombre" class="form-control <?= $validation->hasError('nombre') ? 'is-invalid' : '' ?>" value="<?= esc(old('nombre', $servicio['nombre'] ?? '')) ?>" required>
                        <!-- MEJORA: Se muestra el error de validación -->
                        <?php if ($validation->hasError('nombre')): ?>
                            <div class="invalid-feedback">
                                <?= $validation->getError('nombre') ?>
                            </div>
                        <?php endif; ?>
                        <!-- MEJORA: Texto de ayuda para guiar al usuario -->
                        <div class="form-text">Ej: Barra de Café Americano, Servicio de Meseros (4 horas), etc.</div>
                    </div>

                    <div class="row">
                        <!-- Campo Precio Base -->
                        <div class="col-md-6 mb-3">
                            <label for="precio_base" class="form-label">Precio Base</label>
                            <!-- MEJORA: Input group con icono para mayor claridad visual -->
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="precio_base" id="precio_base" class="form-control <?= $validation->hasError('precio_base') ? 'is-invalid' : '' ?>" value="<?= esc(old('precio_base', $servicio['precio_base'] ?? '')) ?>" required>
                                <?php if ($validation->hasError('precio_base')): ?>
                                    <div class="invalid-feedback">
                                        <?= $validation->getError('precio_base') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-text">El costo si el tipo es 'Fijo' o el costo unitario.</div>
                        </div>

                        <!-- Campo Mínimo de Personas -->
                        <div class="col-md-6 mb-3">
                            <label for="min_personas" class="form-label">Mínimo de Personas</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-people-fill"></i></span>
                                <input type="number" name="min_personas" id="min_personas" class="form-control <?= $validation->hasError('min_personas') ? 'is-invalid' : '' ?>" value="<?= esc(old('min_personas', $servicio['min_personas'] ?? '1')) ?>" required>
                                <?php if ($validation->hasError('min_personas')): ?>
                                    <div class="invalid-feedback">
                                        <?= $validation->getError('min_personas') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-text">Si el cobro es por persona, este es el mínimo a cobrar. Usa 1 si no aplica.</div>
                        </div>
                    </div>

                    <!-- Campo Tipo de Cobro -->
                    <div class="mb-3">
                        <label for="tipo_cobro" class="form-label">Tipo de Cobro</label>
                        <select name="tipo_cobro" id="tipo_cobro" class="form-select <?= $validation->hasError('tipo_cobro') ? 'is-invalid' : '' ?>" required>
                            <option value="">-- Seleccionar --</option>
                            <option value="fijo" <?= old('tipo_cobro', $servicio['tipo_cobro'] ?? '') == 'fijo' ? 'selected' : '' ?>>Fijo (un solo cobro)</option>
                            <option value="por_persona" <?= old('tipo_cobro', $servicio['tipo_cobro'] ?? '') == 'por_persona' ? 'selected' : '' ?>>Por Persona</option>
                            <option value="por_litro" <?= old('tipo_cobro', $servicio['tipo_cobro'] ?? '') == 'por_litro' ? 'selected' : '' ?>>Por Litro (para bebidas)</option>
                        </select>
                         <?php if ($validation->hasError('tipo_cobro')): ?>
                            <div class="invalid-feedback">
                                <?= $validation->getError('tipo_cobro') ?>
                            </div>
                        <?php endif; ?>
                        <div class="form-text">Define cómo se calculará el costo en la cotización.</div>
                    </div>
                </form>
            </div>
            <!-- MEJORA: Botones en el pie de la tarjeta para una mejor separación -->
            <div class="card-footer text-end bg-light">
                <a href="<?= site_url('admin/servicios') ?>" class="btn btn-secondary">Cancelar</a>
                <!-- MEJORA: Botón primario con icono y texto dinámico -->
                <button type="submit" form="main-form" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>
                    <?= $isEditing ? 'Actualizar Servicio' : 'Guardar Servicio' ?>
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>