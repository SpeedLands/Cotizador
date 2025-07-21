<!DOCTYPE html>
<html lang="es">
<head>
    <title>Iniciar Sesión - Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .login-container { max-width: 400px; margin-top: 100px; }
    </style>
</head>
<body>
<div class="container login-container">
    <div class="card shadow-sm">
        <div class="card-body p-5">
            <h3 class="card-title text-center mb-4">Panel de Administración</h3>
            
            <!-- Mostrar mensajes de error -->
            <?php if (session()->get('error')): ?>
                <div class="alert alert-danger" role="alert">
                    <?= session()->get('error') ?>
                </div>
            <?php endif; ?>

            <form action="<?= site_url('login') ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>