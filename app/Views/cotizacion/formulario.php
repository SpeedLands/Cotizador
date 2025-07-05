<?= $this->extend('layouts/default') ?>

<?= $this->section('page_title') ?>
Solicitud de Cotización - Eventos Mapolato
<?= $this->endSection() ?>

<?= $this->section('page_styles') ?>
<style>
    .resultado-presupuesto {
      border: 1px solid #ddd;
      padding: 20px;
      margin-top: 20px;
      background-color: #f9f9f9;
      border-radius: 5px;
    }
    .resultado-presupuesto h2, .resultado-presupuesto h3 {
      color: #c82333; /* Un color similar al de los títulos del form */
    }
    .loading-spinner {
      display: none; /* Oculto por defecto */
      text-align: center;
      margin-top: 20px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<form id="cotizacionForm" class="needs-validation" novalidate>
    <div class="container my-4">
        <!-- <img src="<?= base_url('assets/logo.png') ?>" alt="Logo"> -->
        <div class="card p-4 bg-light">
            <h1 class="text-center mb-4">
                Solicitud de cotización Eventos Mapolato
            </h1>
            <p>
                Gracias por considerar a Eventos Mapolato. Por favor, complete el siguiente formulario para generar una estimación.
            </p>
            <hr />
            <p class="text-danger mb-0">* Indica que la pregunta es obligatoria</p>
        </div>
    </div>

    <?= $this->include('cotizacion/partials/_info_contacto_evento') ?>
    <?= $this->include('cotizacion/partials/_preferencias_cotizacion') ?>
    <?= $this->include('cotizacion/partials/_detalles_adicionales') ?>

    <div class="container mb-5">
        <div class="text-center">
            <button type="submit" id="submitBtn" class="btn btn-danger btn-lg mt-4">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                Enviar Solicitud
            </button>
        </div>
    </div>
</form>

<!-- Div para mostrar el resultado y el spinner -->
<?= $this->include('cotizacion/_resultado_presupuesto') ?>

<?= $this->endSection() ?>


<?= $this->section('page_scripts') ?>
<script>
    // Tu función parseGeminiResponseToHtml (la misma que ya tenías)
    function parseGeminiResponseToHtml(text) {
      // ... (código JS idéntico al original)
      const lines = text.trim().split('\n');
      let htmlOutput = '<div class="container py-4">'; // Contenedor para mejor espaciado

      const totalEstimadoLine = lines.shift()?.trim();
      if (totalEstimadoLine && !isNaN(parseFloat(totalEstimadoLine))) {
        const totalAmount = parseFloat(totalEstimadoLine);
        const formattedTotal = totalAmount.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });
        htmlOutput += `<h2 class="text-center text-danger mb-3">Presupuesto Estimado Total: ${formattedTotal}</h2>`;
      } else {
        if (totalEstimadoLine) lines.unshift(totalEstimadoLine);
        htmlOutput += `<h2 class="text-center text-danger mb-3">Presupuesto</h2>`;
      }

      if (lines.length > 0 && lines[0]?.trim() === '') {
        lines.shift();
      }
      
      let inList = false;
      let contentHtml = '';

      lines.forEach(line => {
        let trimmedLine = line.trim();
        
        if (!trimmedLine && inList) { 
            return; 
        } else if (!trimmedLine) {
            return;
        }

        let processedLine = trimmedLine.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>'); 

        if (processedLine === '<strong>Desglose Detallado de Costos:</strong>') {
          if (inList) {
            contentHtml += '</ul>';
            inList = false;
          }
          contentHtml += `<h3 class="mt-4 mb-3 text-danger">${processedLine}</h3>`;
        } else if (processedLine.startsWith('* ')) {
          if (!inList) {
            contentHtml += '<ul class="list-group list-group-flush">'; 
            inList = true;
          }
          contentHtml += `<li class="list-group-item">${processedLine.substring(2)}</li>`; 
        } else {
          if (inList) {
            contentHtml += '</ul>';
            inList = false;
          }
          contentHtml += `<p class="mt-2">${processedLine}</p>`;
        }
      });

      if (inList) {
        contentHtml += '</ul>';
      }
      
      htmlOutput += contentHtml;
      htmlOutput += '</div>'; 
      return htmlOutput;
    }

    // Script para manejar el formulario
    document.addEventListener('DOMContentLoaded', function () {
      const form = document.getElementById('cotizacionForm');
      const submitButton = document.getElementById('submitBtn');
      const submitButtonSpinner = submitButton.querySelector('.spinner-border');
      const resultadoDiv = document.getElementById('resultadoPresupuesto');
      const loadingDiv = document.getElementById('loading');
      const quotationPreferencesFeedback = document.getElementById('quotationPreferencesFeedback');
      const consumerTypeFeedback = document.getElementById('consumerTypeFeedback');

      form.addEventListener('submit', async function (event) {
        event.preventDefault();
        event.stopPropagation();

        let quotationPreferencesValid = false;
        document.querySelectorAll('input[name="quotationPreferences"]:checked').forEach(() => {
            quotationPreferencesValid = true;
        });
        if (!quotationPreferencesValid) {
            quotationPreferencesFeedback.style.display = 'block';
        } else {
            quotationPreferencesFeedback.style.display = 'none';
        }

        let consumerTypeValid = false;
        document.querySelectorAll('input[name="consumerType"]:checked').forEach(() => {
            consumerTypeValid = true;
        });
        if (!consumerTypeValid) {
            consumerTypeFeedback.style.display = 'block';
        } else {
            consumerTypeFeedback.style.display = 'none';
        }
        
        form.classList.add('was-validated');

        if (!form.checkValidity() || !quotationPreferencesValid || !consumerTypeValid) {
          return;
        }

        submitButton.disabled = true;
        if(submitButtonSpinner) submitButtonSpinner.style.display = 'inline-block';
        loadingDiv.style.display = 'block';
        resultadoDiv.style.display = 'none';
        resultadoDiv.innerHTML = '';

        const formData = new FormData(form);
        const data = {};

        ['howDidYouHear', 'fullName', 'phoneNumber', 'eventAddress', 'eventDate', 
         'eventStartTime', 'foodServiceTime', 'numberOfGuests', 'otherQuotationDetails', 
         'setupDifficulty', 'dietaryRestrictions', 'additionalRequirements', 'budgetRange']
        .forEach(fieldName => {
            data[fieldName] = formData.get(fieldName) || '';
        });
        data.numberOfGuests = parseInt(formData.get('numberOfGuests'), 10) || 0;

        ['eventType', 'tableAndMantel', 'servingStaff', 'coffeeServiceAccess']
        .forEach(fieldName => {
            data[fieldName] = formData.get(fieldName) || 'No especificado';
        });
        
        data.quotationPreferences = [];
        document.querySelectorAll('input[name="quotationPreferences"]:checked').forEach(checkbox => {
          data.quotationPreferences.push(checkbox.value);
        });
         if (data.quotationPreferences.length === 0) {
            data.quotationPreferences = ['Ninguna preferencia específica'];
        }

        data.consumerType = [];
        document.querySelectorAll('input[name="consumerType"]:checked').forEach(checkbox => {
          data.consumerType.push(checkbox.value);
        });
        if (data.consumerType.length === 0) {
            data.consumerType = ['No especificado'];
        }

        try {
          const response = await fetch('<?= site_url("api/catering/estimate") ?>', { // Asegúrate que esta ruta exista en tus Routes.php
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
          });

          loadingDiv.style.display = 'none';
          submitButton.disabled = false;
          if(submitButtonSpinner) submitButtonSpinner.style.display = 'none';

          if (response.ok) {
            const result = await response.json();
            if (result.mensaje) {
              resultadoDiv.innerHTML = parseGeminiResponseToHtml(result.mensaje);
              resultadoDiv.style.display = 'block';
            } else if (result.error) {
              resultadoDiv.innerHTML = `<div class="alert alert-danger">${result.error}</div>`;
              resultadoDiv.style.display = 'block';
            } else {
                resultadoDiv.innerHTML = `<div class="alert alert-warning">Respuesta inesperada del servidor.</div>`;
                resultadoDiv.style.display = 'block';
            }
          } else {
            const errorResult = await response.json().catch(() => null);
            let errorMessage = `Error HTTP ${response.status}: ${response.statusText}`;
            if (errorResult && errorResult.error) {
                errorMessage = errorResult.error;
            } else if (errorResult && errorResult.message) { 
                errorMessage = errorResult.message;
            }
            resultadoDiv.innerHTML = `<div class="alert alert-danger">Error al contactar el servidor: ${errorMessage}</div>`;
            resultadoDiv.style.display = 'block';
            console.error('Error en la respuesta:', response.status, response.statusText, errorResult);
          }
        } catch (error) {
          console.error('Error en fetch:', error);
          loadingDiv.style.display = 'none';
          submitButton.disabled = false;
          if(submitButtonSpinner) submitButtonSpinner.style.display = 'none';
          resultadoDiv.innerHTML = `<div class="alert alert-danger">Ocurrió un error de red o al procesar la solicitud: ${error.message}</div>`;
          resultadoDiv.style.display = 'block';
        }
      });

      var forms = document.querySelectorAll('.needs-validation')
      Array.prototype.slice.call(forms)
        .forEach(function (form) {
          form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
              event.preventDefault()
              event.stopPropagation()
            }
            form.classList.add('was-validated')
          }, false)
        })
    });
</script>
<?= $this->endSection() ?>