<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= esc($titulo) ?></h1>
        <!-- El botón "Crear Evento" ha sido eliminado -->
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div id="calendario-loader" class="text-center p-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
            <div id="calendario" style="visibility: hidden;"></div>
        </div>
    </div>

    <!-- El HTML completo del Modal ha sido eliminado -->

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <!-- Scripts de FullCalendar -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar-bootstrap5@6.1.18/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.18/index.global.min.js'></script>

    // En tu vista del calendario

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendario');
            const loaderEl = document.getElementById('calendario-loader');

            const calendar = new FullCalendar.Calendar(calendarEl, {
                themeSystem: 'bootstrap5',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listWeek'
                },
                buttonText: {
                    today:    'Hoy',
                    month:    'Mes',
                    week:     'Semana',
                    day:      'Dia',
                    list:     'Lista'
                },
                locale: 'es', 
                
                events: '<?= site_url('admin/calendario/eventos') ?>',

                loading: function(isLoading) {
                    if (isLoading) {
                        loaderEl.style.display = 'block';
                        calendarEl.style.visibility = 'hidden';
                    } else {
                        loaderEl.style.display = 'none';
                        calendarEl.style.visibility = 'visible';
                    }
                },
                
                eventDidMount: function(info) {
                    if (info.event.extendedProps.description) {
                        new bootstrap.Popover(info.el, {
                            title: info.event.title,
                            content: info.event.extendedProps.description,
                            placement: 'top',
                            trigger: 'hover',
                            container: 'body'
                        });
                    }
                }
            });

            calendar.render();
        });
    </script>
<?= $this->endSection() ?>