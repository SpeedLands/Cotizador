<div class="container mb-5">
      <div class="card p-4 bg-light">
        <h4 class="mb-4 text-danger">
          <i class="bi bi-ui-checks-grid"></i> Detalles Adicionales del Evento
        </h4>

        <!-- Tipo de evento -->
        <div class="mb-3">
          <label class="form-label fw-bold">Tipo de evento <span class="text-danger">*</span></label>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="eventType" id="eventoSocial" value="Evento Social" required />
            <label class="form-check-label" for="eventoSocial">Evento Social</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="eventType" id="eventoEmpresarial" value="Evento Empresarial o corporativo" required />
            <label class="form-check-label" for="eventoEmpresarial">Evento Empresarial o corporativo</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="eventType" id="eventoOtro" value="Otro" required />
            <label class="form-check-label" for="eventoOtro">Otro</label>
             <div class="invalid-feedback">Por favor selecciona el tipo de evento.</div>
          </div>
        </div>

        <!-- Mesa y mantel -->
        <div class="mb-3">
          <label class="form-label fw-bold">¿Gustan agregar mesa y mantel para servicio? <span class="text-danger">*</span></label>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="tableAndMantel" id="mesaSi" value="Sí" required />
            <label class="form-check-label" for="mesaSi">Sí</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="tableAndMantel" id="mesaNo" value="No, ya tenemos donde montar el servicio" required />
            <label class="form-check-label" for="mesaNo">No, ya tenemos donde montar el servicio</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="tableAndMantel" id="mesaOtros" value="Otros" required />
            <label class="form-check-label" for="mesaOtros">Otros</label>
            <div class="invalid-feedback">Por favor selecciona una opción para mesa y mantel.</div>
          </div>
        </div>

        <!-- Servicio adicional -->
        <div class="mb-3">
          <label class="form-label fw-bold">¿Gusta que alguien este sirviendo en el evento (costo adicional)? <span class="text-danger">*</span></label>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="servingStaff" id="sirviendoSi" value="Sí" required />
            <label class="form-check-label" for="sirviendoSi">Sí</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="servingStaff" id="sirviendoNo" value="No" required />
            <label class="form-check-label" for="sirviendoNo">No</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="servingStaff" id="sirviendoConsiderando" value="Lo estoy considerando, favor de incluirlo" required />
            <label class="form-check-label" for="sirviendoConsiderando">Lo estoy considerando, favor de incluirlo</label>
            <div class="invalid-feedback">Por favor indica si requieres personal de servicio.</div>
          </div>
        </div>

        <!-- Enchufes cercanos -->
        <div class="mb-3">
          <label class="form-label fw-bold">En caso de servicio de café; ¿Hay acceso a enchufes cerca de donde será utilizado?</label>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="coffeeServiceAccess" id="cafeEnchufeSi" value="Sí" />
            <label class="form-check-label" for="cafeEnchufeSi">Sí</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="coffeeServiceAccess" id="cafeEnchufeNo" value="No hay cerca, se necesita extensión" />
            <label class="form-check-label" for="cafeEnchufeNo">No hay cerca, se necesita extensión</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="coffeeServiceAccess" id="cafeEnchufeNA" value="No aplica / No pedí café" checked/> <!-- Opción por defecto si no se pide café -->
            <label class="form-check-label" for="cafeEnchufeNA">No aplica / No pedí café</label>
          </div>
        </div>

        <!-- Dificultad del montaje -->
        <div class="mb-3">
          <label for="setupDifficulty" class="form-label fw-bold">Describir dificultad del montaje <span class="text-danger">*</span></label>
          <textarea class="form-control" id="setupDifficulty" name="setupDifficulty" rows="3" placeholder="Ej: Fácil acceso, 2do piso sin elevador, distancia aprox. desde donde descargaremos..." required></textarea>
          <div class="invalid-feedback">Por favor describe la dificultad del montaje.</div>
        </div>

        <!-- Tipo de consumidores -->
        <div class="mb-3">
          <label class="form-label fw-bold">Tipo de consumidores <span class="text-danger">*</span></label>
          <div class="d-flex flex-wrap gap-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="consumidorHombres" name="consumerType" value="Hombres" />
              <label class="form-check-label" for="consumidorHombres">Hombres</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="consumidorMujeres" name="consumerType" value="Mujeres" />
              <label class="form-check-label" for="consumidorMujeres">Mujeres</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="consumidorNinos" name="consumerType" value="Niños" />
              <label class="form-check-label" for="consumidorNinos">Niños</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="consumidorMixto" name="consumerType" value="Mixto" />
              <label class="form-check-label" for="consumidorMixto">Mixto</label>
            </div>
          </div>
           <div class="invalid-feedback d-block" id="consumerTypeFeedback" style="display:none;">Por favor, selecciona al menos un tipo de consumidor.</div>
        </div>

        <!-- Restricciones alimenticias -->
        <div class="mb-3">
          <label for="dietaryRestrictions" class="form-label">¿Alguna restricción alimenticia?</label>
          <input type="text" class="form-control" id="dietaryRestrictions" name="dietaryRestrictions" placeholder="Ej: Alergias, vegano, sin gluten, etc." />
        </div>

        <!-- Requisitos especiales -->
        <div class="mb-3">
          <label for="additionalRequirements" class="form-label">¿Requisitos adicionales o especiales?</label>
          <textarea class="form-control" id="additionalRequirements" name="additionalRequirements" rows="2" placeholder="Cualquier otro detalle importante"></textarea>
        </div>

        <!-- Presupuesto -->
        <div class="mb-3">
          <label for="budgetRange" class="form-label">¿Tienen algún rango de presupuesto en mente?</label>
          <input type="text" class="form-control" id="budgetRange" name="budgetRange" placeholder="Ej: $15000 - $20000 MXN" />
        </div>
      </div>
    </div>