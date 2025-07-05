<div class="container mb-5">
    <div class="card p-4 bg-light">
        <h3 class="text-danger mb-3">
            <i class="bi bi-calendar4-week"></i> Información de Contacto y Evento
        </h3>
        <div class="row g-3">
            <div class="col-12">
                <label for="howDidYouHear" class="form-label">¿Cómo supiste de nosotros? <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="howDidYouHear" placeholder="Ej: Redes sociales, recomendación, etc." name="howDidYouHear" required />
                <div class="invalid-feedback">Por favor ingresa cómo nos conociste.</div>
            </div>
            <div class="col-md-6">
                <label for="fullName" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="fullName" placeholder="Su nombre completo" name="fullName" required />
                <div class="invalid-feedback">Por favor ingresa tu nombre.</div>
            </div>

            <div class="col-md-6">
                <label for="phoneNumber" class="form-label">Teléfono <span class="text-danger">*</span></label>
                <input type="tel" class="form-control" id="phoneNumber" placeholder="Su número de teléfono" name="phoneNumber" required />
                <div class="invalid-feedback">Por favor ingresa tu número de teléfono.</div>
            </div>

            <div class="col-12">
                <label for="eventAddress" class="form-label">Dirección y referencias del lugar del evento <span class="text-danger">*</span></label>
                <textarea class="form-control" id="eventAddress" placeholder="Dirección completa y detalles relevantes" rows="3" name="eventAddress" required></textarea>
                <div class="invalid-feedback">Por favor ingresa la dirección del evento.</div>
            </div>

            <div class="col-md-6">
                <label for="eventDate" class="form-label">Día del evento <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="eventDate" name="eventDate" required />
                <div class="invalid-feedback">Por favor selecciona la fecha del evento.</div>
            </div>

            <div class="col-md-6">
                <label for="eventStartTime" class="form-label">Hora que comienza el evento <span class="text-danger">*</span></label>
                <input type="time" class="form-control" id="eventStartTime" name="eventStartTime" required />
                <div class="invalid-feedback">Por favor selecciona la hora de inicio del evento.</div>
            </div>

            <div class="col-md-6">
                <label for="foodServiceTime" class="form-label">Horario del consumo de alimentos o bebidas <span class="text-danger">*</span></label>
                <input type="time" class="form-control" id="foodServiceTime" name="foodServiceTime" required />
                <div class="invalid-feedback">Por favor selecciona el horario de consumo.</div>
            </div>
            <div class="col-md-6">
                <label for="numberOfGuests" class="form-label">Cantidad de invitados <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="numberOfGuests" name="numberOfGuests" min="1" required />
                <div class="invalid-feedback">Por favor ingresa la cantidad de invitados.</div>
            </div>
        </div>
    </div>
</div>