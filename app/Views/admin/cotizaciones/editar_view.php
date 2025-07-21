<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
    <h1><?= esc($titulo) ?></h1>
    <p>Modifica los detalles de la cotización y guarda los cambios.</p>
    <hr>

    <form action="<?= site_url('admin/cotizaciones/actualizar') ?>" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="cotizacion_id" value="<?= esc($cotizacion['id']) ?>">

        <?php
            // Aquí reutilizamos los parciales del formulario público, pasándoles
            // los datos que el controlador preparó para que se puedan rellenar.
            echo view('public/partials/_form_cliente_evento', $this->getData());
            echo view('public/partials/_form_servicios', $this->getData());
            echo view('public/partials/_form_detalles_finales', $this->getData());
        ?>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary btn-lg">Guardar Cambios</button>
            <a href="<?= site_url('admin/cotizaciones/ver/' . $cotizacion['id']) ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
<?= $this->endSection() ?>