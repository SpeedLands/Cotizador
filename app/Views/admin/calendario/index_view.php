<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= esc($titulo) ?></h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#eventoModal">
            <i class="bi bi-plus-circle me-1"></i> Crear Evento
        </button>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div id="calendario-loader" class="text-center p-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
            <!-- El calendario estará oculto hasta que se cargue -->
            <div id="calendario" style="visibility: hidden;"></div>
        </div>
    </div>

    <div class="modal fade" id="eventoModal" tabindex="-1" aria-labelledby="eventoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventoModalLabel">Detalles del Evento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Este formulario puede ser para crear o para mostrar detalles -->
                    <form id="formEvento">
                        <input type="hidden" id="eventoId">
                        <div class="mb-3">
                            <label for="eventoTitulo" class="form-label">Título del Evento</label>
                            <input type="text" class="form-control" id="eventoTitulo" required>
                        </div>
                        <div class="mb-3">
                            <label for="eventoFecha" class="form-label">Fecha</label>
                            <input type="date" class="form-control" id="eventoFecha" required>
                        </div>
                        <div class="mb-3">
                            <label for="eventoDescripcion" class="form-label">Descripción (Opcional)</label>
                            <textarea class="form-control" id="eventoDescripcion" rows="3"></textarea>
                        </div>
                    </form>
                    <!-- Sección solo para mostrar detalles -->
                    <div id="detalleEvento" class="d-none">
                        <h4 id="detalleTitulo"></h4>
                        <p><i class="bi bi-calendar-event"></i> <strong id="detalleFecha"></strong></p>
                        <p id="detalleDescripcion"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <!-- El botón de acción cambiará según el contexto -->
                    <button type="submit" form="formEvento" class="btn btn-primary" id="btnAccion">Guardar Evento</button>
                    <a href="#" id="btnVerCotizacion" class="btn btn-info d-none">Ver Cotización</a>
                </div>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar-bootstrap5@6.1.18/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.18/index.global.min.js'></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendario');
            const loaderEl = document.getElementById('calendario-loader');
            const eventoModal = new bootstrap.Modal(document.getElementById('eventoModal'));
            const eventoForm = document.getElementById('formEvento');

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
                editable: true, // Permite arrastrar y soltar eventos (requiere lógica en backend)
                selectable: true, // Permite seleccionar fechas

                // Al hacer clic en una fecha vacía
                dateClick: function(info) {
                    eventoForm.reset();
                    document.getElementById('eventoId').value = '';
                    document.getElementById('eventoModalLabel').innerText = 'Crear Nuevo Evento';
                    document.getElementById('eventoFecha').value = info.dateStr;
                    eventoModal.show();
                },
                
                // Al hacer clic en un evento existente
                eventClick: function(info) {
                    info.jsEvent.preventDefault(); 
                    
                    // Mostramos la info en el modal
                    document.getElementById('eventoModalLabel').innerText = 'Detalles del Evento';
                    document.getElementById('eventoId').value = info.event.id;
                    document.getElementById('eventoTitulo').value = info.event.title;
                    
                    // Formateamos la fecha correctamente para el input type="date"
                    const eventDate = new Date(info.event.start);
                    const formattedDate = eventDate.toISOString().split('T')[0];
                    document.getElementById('eventoFecha').value = formattedDate;

                    document.getElementById('eventoDescripcion').value = info.event.extendedProps.description || '';
                    
                    // Si el evento tiene una URL (ej: a la cotización), mostramos el botón
                    const btnVerCotizacion = document.getElementById('btnVerCotizacion');
                    if (info.event.url) {
                        btnVerCotizacion.href = info.event.url;
                        btnVerCotizacion.classList.remove('d-none');
                    } else {
                        btnVerCotizacion.classList.add('d-none');
                    }

                    eventoModal.show();
                },

                loading: function(isLoading) {
                    if (isLoading) {
                        loaderEl.style.display = 'block';
                        calendarEl.style.visibility = 'hidden';
                    } else {
                        loaderEl.style.display = 'none';
                        calendarEl.style.visibility = 'visible';
                    }
                },
                // Popover al pasar el ratón (requiere Popper.js, ya incluido en Bootstrap Bundle)
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

            // Lógica para guardar el formulario del modal
            eventoForm.addEventListener('submit', function(e) {
                e.preventDefault();

                // no probable de implementacion
                console.log('Formulario enviado');
                eventoModal.hide();
                calendar.refetchEvents(); // Recarga los eventos después de guardar
            });
        });
    </script>
<?= $this->endSection() ?>