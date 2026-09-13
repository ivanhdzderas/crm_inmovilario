<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0 rounded-3">
                <div class="card-header bg-primary text-white p-3">
                    <h5 class="mb-0"><i class="fa fa-calculator me-2"></i> Calculadora Estimada de ISR</h5>
                    <small class="opacity-75">Estimación por Enajenación de Bienes Inmuebles</small>
                </div>
                <div class="card-body p-4">
                    <form id="isrForm">
                        <!-- Precio de Venta -->
                        <div class="mb-3">
                            <label for="sale_price" class="form-label fw-bold">1. ¿En cuánto la quiere vender?</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" class="form-control currency-input form-control-lg" id="sale_price" required placeholder="0.00">
                            </div>
                        </div>

                        <!-- Costo de Adquisición -->
                        <div class="mb-3">
                            <label for="acquisition_cost" class="form-label fw-bold">2. ¿Cuánto le salió (Costo adquisición)?</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" class="form-control currency-input" id="acquisition_cost" required placeholder="0.00">
                            </div>
                            <div class="form-text text-muted">
                                Si conoce el costo actualizado por notario (con ajuste de inflación), úselo. Si no, ingrese el costo original.
                            </div>
                        </div>

                        <!-- Gastos Deducibles -->
                        <div class="mb-3">
                            <label for="deductions" class="form-label fw-bold">3. Gastos Deducibles (Opcional)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" class="form-control currency-input" id="deductions" placeholder="Notario, Comisiones, Mejoras...">
                            </div>
                        </div>

                        <!-- Años de Tenencia -->
                        <div class="mb-4">
                            <label for="years_held" class="form-label fw-bold">4. Años que ha tenido la propiedad</label>
                            <input type="number" class="form-control" id="years_held" value="1" min="1" max="20" required>
                            <div class="form-text">Esto ayuda a calcular la tasa efectiva del impuesto.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fa fa-calculator me-1"></i> Calcular Impuestos
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="btnLimpiar">
                                Limpiar
                            </button>
                        </div>
                    </form>

                    <!-- Resultados -->
                    <div id="resultSection" class="mt-4" style="display:none;">
                        <hr>
                        <h5 class="text-center mb-3 text-dark">Resultado Estimado</h5>
                        
                        <div class="row mb-2">
                            <div class="col-6 text-muted">Utilidad (Ganancia):</div>
                            <div class="col-6 text-end fw-bold text-success" id="resProfit">$0.00</div>
                        </div>
                        
                        <div class="card bg-light border-danger mb-3">
                            <div class="card-body text-center p-3">
                                <small class="text-uppercase text-muted fw-bold">ISR A Pagar (Estimado)</small>
                                <h2 class="text-danger mb-0 display-6 fw-bold" id="resTax">$0.00</h2>
                                <small class="text-muted" id="resEffectiveRate">(0.00% de la venta)</small>
                            </div>
                        </div>

                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <i class="fa fa-exclamation-triangle fa-2x me-3"></i>
                            <div>
                                <small><strong>Nota Legal:</strong> Este cálculo es meramente informativo y <u>no sustituye el cálculo oficial de un notario</u>. No contempla exención por casa habitación (hasta 700k UDIS) ni ajuste exacto por INPC.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Formateo de moneda
    const currencyInputs = document.querySelectorAll('.currency-input');
    
    currencyInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            // Eliminar caracteres no numéricos excepto punto
            let value = this.value.replace(/[^0-9.]/g, '');
            // Evitar múltiples puntos
            const parts = value.split('.');
            if (parts.length > 2) value = parts[0] + '.' + parts.slice(1).join('');
            this.value = value;
        });

        input.addEventListener('blur', function(e) {
            const val = parseFloat(this.value);
            if (!isNaN(val)) {
                this.value = val.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
        });
        
        input.addEventListener('focus', function(e) {
            const val = this.value.replace(/,/g, '');
            this.value = val;
        });
    });

    // Tarifa ISR Anual 2024 (Ejemplo simplificado)
    // Límite Inferior | Cuota Fija | % Sobre Excedente
    const isrTable2024 = [
        { limit: 0.01, fixed: 0.00, percent: 0.0192 },
        { limit: 9907.87, fixed: 190.23, percent: 0.0640 },
        { limit: 84090.24, fixed: 4937.90, percent: 0.1088 },
        { limit: 147823.59, fixed: 11872.12, percent: 0.1600 },
        { limit: 171783.85, fixed: 15705.76, percent: 0.1792 },
        { limit: 205625.35, fixed: 21770.16, percent: 0.2136 },
        { limit: 414704.13, fixed: 66432.39, percent: 0.2352 },
        { limit: 653556.03, fixed: 122611.36, percent: 0.3000 },
        { limit: 1248161.91, fixed: 300993.12, percent: 0.3200 },
        { limit: 3744485.73, fixed: 1099816.74, percent: 0.3400 },
        { limit: 11233457.17, fixed: 3646067.03, percent: 0.3500 }
    ];

    function calculateISR(taxableBase) {
        if (taxableBase <= 0) return 0;
        
        let bracket = isrTable2024[0];
        for (let i = 0; i < isrTable2024.length; i++) {
            if (taxableBase >= isrTable2024[i].limit) {
                bracket = isrTable2024[i];
            } else {
                break;
            }
        }
        
        const excess = taxableBase - bracket.limit;
        const marginalTax = excess * bracket.percent;
        return bracket.fixed + marginalTax;
    }

    document.getElementById('isrForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Obtener valores limpios
        const salePrice = parseFloat(document.getElementById('sale_price').value.replace(/,/g, '')) || 0;
        const acquisitionCost = parseFloat(document.getElementById('acquisition_cost').value.replace(/,/g, '')) || 0;
        const deductions = parseFloat(document.getElementById('deductions').value.replace(/,/g, '')) || 0;
        let years = parseInt(document.getElementById('years_held').value) || 1;
        if (years > 20) years = 20; // Tope legal común para cálculo

        // 1. Calcular Utilidad
        const profit = salePrice - acquisitionCost - deductions;

        let tax = 0;
        
        if (profit > 0) {
            // 2. Calcular Base Gravable Anual (Utilidad / Años)
            // Según Art 126 LISR para pago provisional
            const annualBase = profit / years;
            
            // 3. Calcular ISR sobre esa base anual
            const annualTax = calculateISR(annualBase);
            
            // 4. Multiplicar por los años para obtener el total provisional
            tax = annualTax * years;
        }

        // Mostrar Resultados
        const formatter = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });
        
        document.getElementById('resProfit').textContent = formatter.format(profit);
        if (profit <= 0) {
            document.getElementById('resProfit').className = 'col-6 text-end fw-bold text-danger';
            document.getElementById('resProfit').textContent += ' (Pérdida)';
        } else {
            document.getElementById('resProfit').className = 'col-6 text-end fw-bold text-success';
        }

        document.getElementById('resTax').textContent = formatter.format(tax);
        
        // Calcular tasa efectiva
        let effectiveRate = 0;
        if (salePrice > 0) {
            effectiveRate = (tax / salePrice) * 100;
        }
        document.getElementById('resEffectiveRate').textContent = `(${effectiveRate.toFixed(2)}% del precio de venta)`;

        document.getElementById('resultSection').style.display = 'block';
        
        // Scroll suave hacia resultados
        document.getElementById('resultSection').scrollIntoView({ behavior: 'smooth' });
    });

    document.getElementById('btnLimpiar').addEventListener('click', function() {
        document.getElementById('isrForm').reset();
        document.getElementById('resultSection').style.display = 'none';
    });
});
</script>
<?= $this->endSection() ?>