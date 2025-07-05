<div class="container mb-5">
    <div class="card p-4 bg-light">
        <h4 class="mb-3 text-danger">
            <i class="bi bi-clipboard-check"></i> Preferencias de Cotización
        </h4>
        <h6 class="fw-bold">
            ¿Qué gustas en tu cotización? <span class="text-danger">*</span>
        </h6>
        <p class="text-muted">Selecciona todas las opciones que apliquen.</p>
        <div class="row row-cols-1 row-cols-md-2 g-3">
            <!-- Columna izquierda -->
            <div class="col">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="chaferDesayuno" name="quotationPreferences" value="Desayuno en chafers (baños María)" />
              <label class="form-check-label" for="chaferDesayuno">Desayuno en chafers (baños María)</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="chaferComida" name="quotationPreferences" value="Comidas/Cenas en chafers (baños maría)" />
              <label class="form-check-label" for="chaferComida">Comidas/Cenas en chafers (baños maría)</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="canapes" name="quotationPreferences" value="Mesa de bocadillos/canapés (mínimo 30 personas)" />
              <label class="form-check-label" for="canapes">Mesa de bocadillos/canapés (mínimo 30 personas)</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="snacks" name="quotationPreferences" value="Mesa de snacks (papitas y dulces) (mínimo 30 personas)" />
              <label class="form-check-label" for="snacks">Mesa de snacks (papitas y dulces) (mínimo 30 personas)</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="cafe" name="quotationPreferences" value="Estación de Café" />
              <label class="form-check-label" for="cafe">Estación de Café</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="charcuteria10" name="quotationPreferences" value="Tabla de charcutería (Arriba de 10 personas)" />
              <label class="form-check-label" for="charcuteria10">Tabla de charcutería (Arriba de 10 personas)</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="charcuteria15" name="quotationPreferences" value="Charcutería individual (Arriba de 15 personas)" />
              <label class="form-check-label" for="charcuteria15">Charcutería individual (Arriba de 15 personas)</label>
            </div>
          </div>
            <!-- Columna derecha -->
            <div class="col">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="cazuelaDesayuno" name="quotationPreferences" value="Desayuno en Cazuelas mexicanas" />
              <label class="form-check-label" for="cazuelaDesayuno">Desayuno en Cazuelas mexicanas</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="cazuelaComida" name="quotationPreferences" value="Comida/Cena en Cazuelas mexicanas" />
              <label class="form-check-label" for="cazuelaComida">Comida/Cena en Cazuelas mexicanas</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="postres" name="quotationPreferences" value="Mesa de Postres (mínimo 30 personas)" />
              <label class="form-check-label" for="postres">Mesa de Postres (mínimo 30 personas)</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="ensaladas" name="quotationPreferences" value="Barra de ensaladas (mínimo 30 personas)" />
              <label class="form-check-label" for="ensaladas">Barra de ensaladas (mínimo 30 personas)</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="lunchBox" name="quotationPreferences" value="Lunch box" />
              <label class="form-check-label" for="lunchBox">Lunch box</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="charcuteria50" name="quotationPreferences" value="Mesa de charcutería (Arriba de 50 personas)" />
              <label class="form-check-label" for="charcuteria50">Mesa de charcutería (Arriba de 50 personas)</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="bebida" name="quotationPreferences" value="Bebida en dispensador (aguas de sabor)" />
              <label class="form-check-label" for="bebida">Bebida en dispensador (aguas de sabor)</label>
            </div>
          </div>
        </div>
            <div class="col-12">
                <div class="invalid-feedback d-block" id="quotationPreferencesFeedback" style="display:none;">Por favor, selecciona al menos una preferencia.</div>
            </div>
        </div>
        <div class="mt-4">
            <label for="otherQuotationDetails" class="form-label">Otros detalles para la cotización</label>
            <textarea class="form-control" id="otherQuotationDetails" name="otherQuotationDetails" rows="3" placeholder="Si tienes alguna otra preferencia o detalle, escríbelo aquí."></textarea>
        </div>
    </div>
</div>