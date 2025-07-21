<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= esc($titulo) ?? 'Panel de Administración' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- CSS Personalizado -->
    <style>
        body {
            background-color: #f8f9fa; /* Un gris muy claro para el fondo */
        }
        
        .navbar-brand img {
            max-height: 40px; /* Ajustamos la altura del logo */
            width: auto;
        }

        /* Estilo para los enlaces de navegación */
        .navbar-nav .nav-link {
            transition: all 0.3s ease;
            border-radius: 0.25rem;
            margin: 0 0.25rem;
        }

        /* Efecto hover más sutil */
        .navbar-nav .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        /* Estilo para el enlace activo (¡Importante para la usabilidad!) */
        .navbar-nav .nav-link.active {
            background-color: #0d6efd; /* Color primario de Bootstrap */
            color: #fff;
            font-weight: 500;
        }

        .dropdown-item:active {
            background-color: #0d6efd;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <!-- Logo -->
            <a class="navbar-brand" href="<?= site_url('admin') ?>">
                <img src="<?= base_url('assets/logo.png'); ?>" alt="Logo">
            </a>

            <!-- Botón para menú responsive (hamburguesa) -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Enlaces de navegación -->
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <!-- Para que funcione el ".active", necesitarías una variable que indique la página actual -->
                    <?php $uri = current_url(true)->getSegment(2); // Obtiene el segundo segmento de la URL, ej: 'cotizaciones' ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($uri == 'admin' || $uri == '') ? 'active' : '' ?>" href="<?= site_url('admin') ?>">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($uri == 'cotizaciones') ? 'active' : '' ?>" href="<?= site_url('admin/cotizaciones') ?>">Cotizaciones</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($uri == 'calendario') ? 'active' : '' ?>" href="<?= site_url('admin/calendario') ?>">Calendario</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($uri == 'servicios') ? 'active' : '' ?>" href="<?= site_url('admin/servicios') ?>">Servicios</a>
                    </li>
                </ul>
                
                <!-- Menú de usuario a la derecha -->
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                           <i class="bi bi-person-circle"></i> Usuario
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="#">Mi Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= site_url('logout') ?>"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container mt-4">
        <!-- Aquí se inyectará el contenido de cada página -->
        <?= $this->renderSection('content') ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>