<?= $this->extend('template') ?>

<?= $this->section('content'); ?>
<?php helper('prospect'); ?>
<?php $isFunerarioList = ! empty($isFunerarioList); ?>
<div class="container-fluid py-4">
    <div class="card shadow-lg border-0 rounded-3">
        <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom p-3">
            <h1 class="card-title text-primary mb-0"><?= esc($listTitle ?? 'Gestión de Prospectos (CRM)') ?></h1>
            <button type="button" class="btn btn-success shadow-sm rounded-pill px-4 btn-offcanvas-create"
                data-url="<?= site_url('prospects/create' . ($isFunerarioList ? '?interest=funerario' : '')) ?>"
                data-funerario="<?= $isFunerarioList ? '1' : '0' ?>">
                <i class="fa fa-plus-circle me-2"></i> <?= $isFunerarioList ? 'Agregar comprador funerario' : 'Agregar Nuevo Prospecto' ?>
            </button>
        </div>
        
        <div class="card-body p-3">
            <!-- Mensajes de éxito o error (ej. después de guardar o actualizar) -->
            <?php if (session()->getFlashdata('success')): ?>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: <?= json_encode(session()->getFlashdata('success')) ?>,
                        confirmButtonColor: '#28a745',
                        timer: 3000,
                        timerProgressBar: true
                    });
                });
                </script>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    <?php 
                        $errors = session()->getFlashdata('error');
                        // Si es un arreglo (varios errores), los unimos con saltos de línea
                        $errorText = is_array($errors) ? implode("\n", $errors) : $errors;
                    ?>
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: <?= json_encode($errorText) ?>,
                        confirmButtonColor: '#dc3545'
                    });
                });
                </script>
            <?php endif; ?>

            <table class="table table-striped table-hover align-middle mb-0" id="prospectsTable" style="width:100%">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th class="text-uppercase text-xs font-weight-bolder opacity-7 p-2">ID</th>
                            <th class="text-uppercase text-xs font-weight-bolder opacity-7">Prospecto</th>
                            <?php if ($isFunerarioList): ?>
                            <th class="text-uppercase text-xs font-weight-bolder opacity-7">Paquete(s)</th>
                            <?php else: ?>
                            <th class="text-uppercase text-xs font-weight-bolder opacity-7">Crédito</th>
                            <th class="text-uppercase text-xs font-weight-bolder opacity-7">Zona</th>
                            <?php endif; ?>
                            <th class="text-uppercase text-xs font-weight-bolder opacity-7">Origen</th>
                            <th class="text-uppercase text-xs font-weight-bolder opacity-7">Asesor</th>
                            <th class="text-uppercase text-xs font-weight-bolder opacity-7">Estatus</th>
                            <th class="text-uppercase text-xs font-weight-bolder opacity-7 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($prospects)): ?>
                            <?php foreach ($prospects as $prospect): ?>
                                <tr data-id="<?= esc($prospect['id']) ?>">
                                    <td class="p-2">
                                        <p class="text-sm font-weight-bold mb-0">#<?= esc($prospect['id']) ?></p>
                                    </td>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm"><?= esc($prospect['full_name']) ?></h6>
                                                <p class="text-xs mb-0">
                                                    <?php if (! empty($prospect['is_funeral_prospect']) || $isFunerarioList): ?>
                                                        <span class="badge bg-secondary text-white border">Funerario</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-dark border"><?= esc(ucfirst($prospect['client_type'] ?? 'comprador')) ?></span>
                                                    <?php endif; ?>
                                                </p>
                                                <p class="text-xs text-secondary mb-0"><?= esc($prospect['email']) ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <?php if ($isFunerarioList): ?>
                                    <td class="col-funeral-packages">
                                        <?php if (! empty($prospect['funeral_packages'])): ?>
                                            <p class="text-sm mb-0"><?= esc($prospect['funeral_packages']) ?></p>
                                        <?php else: ?>
                                            <span class="text-muted small">Sin paquete aún</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php else: ?>
                                    <td>
                                        <p class="text-sm font-weight-bold mb-0 text-success">$<?= esc(number_format($prospect['credit_amount'] ?? 0, 2)) ?></p>
                                    </td>
                                    <td>
                                        <p class="text-sm mb-0 text-secondary"><?= esc($prospect['zone_name']) ?></p>
                                    </td>
                                    <?php endif; ?>
                                    <td>
                                        <?php if (! empty($prospect['acquisition_source_name'])): ?>
                                            <p class="text-sm mb-0">
                                                <?php if (! empty($prospect['acquisition_source_icon'])): ?>
                                                    <i class="<?= esc($prospect['acquisition_source_icon']) ?> me-1"></i>
                                                <?php endif; ?>
                                                <?= esc($prospect['acquisition_source_name']) ?>
                                            </p>
                                            <?php if (! empty($prospect['acquisition_detail'])): ?>
                                                <small class="text-muted"><?= esc($prospect['acquisition_detail']) ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <p class="text-sm mb-0 text-dark"><?= esc($prospect['user_name']) ?></p>
                                    </td>
                                    <td>
                                        <?php 
                                            $badge_color = 'secondary';
                                            $text_color = 'text-white';
                                            if ($prospect['status'] == 'Nuevo') { 
                                                $badge_color = 'primary'; 
                                            } else if ($prospect['status'] == 'Cierre') { 
                                                $badge_color = 'success'; 
                                            } else if (prospect_is_cancelled($prospect['status'])) { 
                                                $badge_color = 'danger'; 
                                            } else if ($prospect['status'] == 'Seguimiento') { 
                                                $badge_color = 'warning'; 
                                                $text_color = 'text-dark';
                                            } else if ($prospect['status'] == 'Cliente') {
                                                $badge_color = 'info';
                                            }
                                        ?>
                                        <span class="badge bg-<?= $badge_color ?> <?= $text_color ?> text-xs font-weight-bold p-2 rounded-pill">
                                            <?= esc(prospect_status_label($prospect['status'])) ?>
                                        </span>
                                        <?php if (prospect_is_cancelled($prospect['status']) && ! empty($prospect['deactivation_reason_name'])): ?>
                                            <button type="button" class="btn btn-link btn-sm p-0 ms-1 align-middle prospect-cancel-info"
                                                <?= prospect_cancellation_popover_attrs(
                                                    $prospect['deactivation_reason_name'] ?? null,
                                                    $prospect['deactivation_notes'] ?? null,
                                                    $prospect['deactivated_at'] ?? null
                                                ) ?>>
                                                <i class="fa fa-info-circle text-danger" title="Ver motivo"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle text-center">
                                        <button type="button"
                                            data-id="<?= esc($prospect['id']) ?>"
                                            class="btn btn-sm btn-info text-white rounded-pill px-3 py-1 btn-offcanvas-detail"
                                            title="Ver Detalle y Seguimiento">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            
            <!-- Offcanvas para detalle del prospecto -->
            <div class="offcanvas offcanvas-end" tabindex="-1" id="prospectOffcanvas" aria-labelledby="prospectOffcanvasLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="prospectOffcanvasLabel">Detalle del Prospecto</h5>
                    <button type="button" class="close" data-dismiss="offcanvas" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="offcanvas-body p-3" id="prospectOffcanvasBody">
                    <div id="prospectOffcanvasLoading" class="text-center my-5">
                        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div>
                        <p class="mt-3 text-muted">Cargando detalle...</p>
                    </div>
                    <div id="prospectOffcanvasContent" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts') ?>
<!-- Script para inicializar DataTables -->
<script>
    // Se espera que jQuery y las librerías de DataTables estén cargadas en 'template'
    $(document).ready(function() {
        var isFunerarioList = <?= json_encode($isFunerarioList) ?>;
        var actionsCol = isFunerarioList ? 6 : 7;
        var hideOnMobile = isFunerarioList ? [2, 3, 4, 5] : [2, 3, 4, 5, 6];

        window.isFunerarioProspectsList = isFunerarioList;
        window.updateProspectFuneralPackagesCell = function(clientId, html) {
            if (!isFunerarioList || !window.prospectsTable || !clientId) return;
            var rowNode = document.querySelector('#prospectsTable tr[data-id="' + clientId + '"]');
            if (!rowNode) return;
            var cell = rowNode.querySelector('td.col-funeral-packages');
            if (cell) {
                cell.innerHTML = html;
            }
        };

      window.prospectsTable = new DataTable('#prospectsTable', {
              "paging": true,
              "ordering": true,
              "info": true,
              "searching": true,
              "responsive": true,
              "scrollX": false,
              "autoWidth": false,
              "lengthChange": false,
              "pageLength": 25,
              "order": [[0, "desc"]], // Ordenar por ID descendente (nuevos primero)
              "columnDefs": [
                  { "orderable": false, "targets": actionsCol },
                  { "className": "text-center", "targets": actionsCol },
                  { "responsivePriority": 1, "targets": 0 },
                  { "responsivePriority": 2, "targets": 1 },
                  { "responsivePriority": 3, "targets": actionsCol },
                  { "responsivePriority": 10001, "targets": hideOnMobile },
                  { "type": "num", "targets": 0 },
                  { "width": "60px", "targets": 0 },
                  { "width": "120px", "targets": actionsCol }
              ],
              "drawCallback": function(settings) {
                  var api = this.api();
                  if (api.order()[0][0] !== 0 || api.order()[0][1] !== 'desc') {
                      api.order([0, 'desc']).draw('page');
                  }
                  if (typeof $ !== 'undefined' && $.fn.popover) {
                      $('.prospect-cancel-info[data-toggle="popover"]').popover({ container: 'body' });
                  }
              },
              "language": {
                 // Archivo de idioma en español (ajustado a la versión de DataTables)
                 // Si el CDN falla, DataTables usará su texto por defecto.
                "url": "<?= base_url('js/es-ES.json') ?>",
                 // Mensaje personalizado cuando la tabla está vacía (incluye icono)
                 "emptyTable": "<div class='text-center text-muted py-3'><i class='fa fa-user-slash fa-2x d-block mb-2'></i> Sin prospectos registrados</div>",
                 "paginate": {
                     "first": "Primero",
                     "last": "Último", 
                     "next": "Siguiente",
                     "previous": "Anterior"
                 },
                 "lengthMenu": "Mostrar _MENU_ registros por página",
                 "zeroRecords": "No se encontraron resultados",
                 "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                 "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                 "infoFiltered": "(filtrado de _MAX_ registros totales)",
                 "search": "Buscar:",
                 "loadingRecords": "Cargando...",
                 "processing": "Procesando..."
              },
              "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
              "initComplete": function() {
                  // Mejorar el estilo después de inicializar y asegurar orden
                  $('.dataTables_filter input').addClass('form-control form-control-sm');
                  $('.dataTables_length select').addClass('form-select form-select-sm');
                  
                  // Forzar orden inicial por ID descendente
                  this.api().order([0, 'desc']).draw('page');

                  if (typeof $ !== 'undefined' && $.fn.popover) {
                      $('[data-toggle="popover"]').popover({ container: 'body' });
                  }
              },
        });
    });
</script>
<script>
    // Offcanvas: cargar detalle del prospecto vía AJAX
    $(function() {
        var offcanvasEl = document.getElementById('prospectOffcanvas');

        // Fallback offcanvas implementation compatible con Bootstrap 4
        // Si Bootstrap 5 está disponible, podría usarse su Offcanvas; aquí aplicamos un control propio
        function showOffcanvas() {
            if (!offcanvasEl) { console.error('Offcanvas element not found'); return; }
            // Ensure visible
            offcanvasEl.style.display = 'block';
            offcanvasEl.classList.add('show-offcanvas');
            offcanvasEl.setAttribute('aria-hidden', 'false');
            offcanvasEl.style.transform = 'translateX(0)';
            document.body.style.overflow = 'hidden';
        }

        function hideOffcanvas() {
            if (!offcanvasEl) { return; }
            offcanvasEl.classList.remove('show-offcanvas');
            offcanvasEl.setAttribute('aria-hidden', 'true');
            offcanvasEl.style.transform = 'translateX(100%)';
            document.body.style.overflow = '';
            // Hide after transition
            setTimeout(function(){ offcanvasEl.style.display = ''; }, 310);
            console.log('Offcanvas hidden');
        }

        // Expose helpers globally so injected views can close/open the offcanvas
        try {
            window.showProspectOffcanvas = showOffcanvas;
            window.hideProspectOffcanvas = hideOffcanvas;
        } catch (e) {}

        // Small notification helper (creates a transient alert on the top-right)
        window.showNotice = function(message, type) {
            type = type || 'info';
            var colors = { success: 'bg-success text-white', info: 'bg-info text-white', danger: 'bg-danger text-white', warning: 'bg-warning text-dark' };
            var cls = colors[type] || colors.info;
            var $container = $('#__notice_container');
            if ($container.length === 0) {
                $container = $('<div id="__notice_container" style="position:fixed; top:16px; right:16px; z-index:2000;"></div>');
                $('body').append($container);
            }
            var $alert = $('<div class="alert '+cls+' shadow-sm" style="min-width:200px;">'+message+'</div>');
            $container.append($alert);
            setTimeout(function(){ $alert.fadeOut(300, function(){ $alert.remove(); }); }, 2200);
        };

        // Close button handler inside offcanvas
        $(offcanvasEl).on('click', '[data-bs-dismiss]', function() {
            hideOffcanvas();
        });

        function initAgendaToggle(root) {
            // Re-attach the agenda toggle behavior inside the loaded content
            var checkbox = root.querySelector('#has_pending');
            var agendaFields = root.querySelector('#agenda_fields');
            var activityTitleInput = root.querySelector('#activity_title');
            var startTimeInput = root.querySelector('#start_time');

            if (!checkbox || !agendaFields) return;

            function toggleAgendaFields() {
                if (checkbox.checked) {
                    agendaFields.style.display = 'block';
                    if (activityTitleInput) activityTitleInput.setAttribute('required', 'required');
                    if (startTimeInput) startTimeInput.setAttribute('required', 'required');
                } else {
                    agendaFields.style.display = 'none';
                    if (activityTitleInput) activityTitleInput.removeAttribute('required');
                    if (startTimeInput) startTimeInput.removeAttribute('required');
                }
            }

            checkbox.addEventListener('change', toggleAgendaFields);
            // Run once to set initial state
            toggleAgendaFields();
        }

        $(document).on('click', '.btn-offcanvas-detail', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var id = $(this).data('id');
            if (!id) return;

            // Show offcanvas with loading state
            $('#prospectOffcanvasLabel').text('Detalle del Prospecto #' + id);
            $('#prospectOffcanvasLoading').show();
            $('#prospectOffcanvasContent').hide().html('');
            showOffcanvas();

            // Fetch the view HTML
            $.get("<?= site_url('prospects/view') ?>/" + id + (<?= json_encode(($listFilter ?? '') === 'funerario') ?> ? '?tab=funerario' : ''))
                .done(function(html) {
                    // Inject content
                    $('#prospectOffcanvasContent').html(html);
                    // Hide loader and show content
                    $('#prospectOffcanvasLoading').hide();
                    $('#prospectOffcanvasContent').show();

                    if (<?= json_encode(($listFilter ?? '') === 'funerario') ?>) {
                        setTimeout(function() {
                            document.getElementById('comprador-funerario-tab')?.click();
                        }, 50);
                    }

                    // After injecting, initialize specific behaviors inside the offcanvas
                    // 1) Attach agenda toggle behavior
                    var root = document.getElementById('prospectOffcanvasContent');
                    initAgendaToggle(root);

                    // 2) Initialize currency formatting if available (for any .currency-input fields)
                    if (window.initCurrencyFormatting) {
                        try { window.initCurrencyFormatting(root); } catch (err) { console.warn('initCurrencyFormatting failed', err); }
                    }

                    // 3) Initialize phone formatting if available (for any .phone-input fields)
                    if (window.initPhoneFormatting) {
                        try { window.initPhoneFormatting(root); } catch (err) { console.warn('initPhoneFormatting failed', err); }
                    }

                    // 4) Bind any in-view offcanvas-close links to close the panel instead of navigating
                    try {
                        $(root).find('[data-offcanvas-close]').on('click', function(e){ e.preventDefault(); hideOffcanvas(); });
                    } catch (errClose) { /* ignore if jQuery not available */ }

                    if (typeof $ !== 'undefined' && $.fn.popover) {
                        $(root).find('[data-toggle="popover"]').popover({ container: 'body' });
                    }

                    // Optionally, re-enable any bootstrap tooltips or other components here
                })
                .fail(function() {
                    $('#prospectOffcanvasLoading').hide();
                    $('#prospectOffcanvasContent').html('<div class="alert alert-danger">No se pudo cargar el detalle. Intenta recargar la página.</div>').show();
                });
        });

        // Open create form in the same offcanvas
        $(document).on('click', '.btn-offcanvas-create', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var url = $(this).data('url') || "<?= site_url('prospects/create') ?>";
            var isFunerarioCreate = $(this).data('funerario') === 1 || $(this).data('funerario') === '1';

            $('#prospectOffcanvasLabel').text(isFunerarioCreate ? 'Nuevo comprador funerario' : 'Nuevo Prospecto');
            $('#prospectOffcanvasLoading').show();
            $('#prospectOffcanvasContent').hide().html('');
            showOffcanvas();

            $.ajax({ url: url, method: 'GET', dataType: 'html' })
                .done(function(html) {
                    console.log('Create form loaded via AJAX');
                    $('#prospectOffcanvasContent').html(html);
                    $('#prospectOffcanvasLoading').hide();
                    $('#prospectOffcanvasContent').show();

                    // If the loaded form has any JS behaviors, init them here
                    var root = document.getElementById('prospectOffcanvasContent');
                    // Example: if the create form has selects or datepickers, initialize here.
                    // Initialize currency inputs inside the injected form
                    if (window.initCurrencyFormatting) {
                        try { window.initCurrencyFormatting(root); } catch (err) { console.warn('initCurrencyFormatting failed', err); }
                    }
                    // Initialize phone inputs inside the injected form
                    if (window.initPhoneFormatting) {
                        try { window.initPhoneFormatting(root); } catch (err) { console.warn('initPhoneFormatting failed', err); }
                    }
                    try {
                        $(root).find('[data-offcanvas-close]').on('click', function(e){ e.preventDefault(); hideOffcanvas(); });
                    } catch (errClose) { /* ignore if jQuery not available */ }
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    console.error('Error loading create form:', textStatus, errorThrown);
                    $('#prospectOffcanvasLoading').hide();
                    $('#prospectOffcanvasContent').html('<div class="alert alert-danger">No se pudo cargar el formulario. Revisa la consola (network).</div>').show();
                });
        });

            // Delegated handler: abrir formulario de edición en offcanvas
            $(document).on('click', '.btn-offcanvas-edit', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var url = $(this).data('url') || null;
                if (!url) return;

                $('#prospectOffcanvasLabel').text('Editar Prospecto');
                $('#prospectOffcanvasLoading').show();
                $('#prospectOffcanvasContent').hide().html('');
                showOffcanvas();

                $.ajax({ url: url, method: 'GET', dataType: 'html' })
                    .done(function(html) {
                        $('#prospectOffcanvasContent').html(html);
                        $('#prospectOffcanvasLoading').hide();
                        $('#prospectOffcanvasContent').show();
                        var root = document.getElementById('prospectOffcanvasContent');
                        if (window.initCurrencyFormatting) { try { window.initCurrencyFormatting(root); } catch (err) { console.warn(err); } }
                        if (window.initPhoneFormatting) { try { window.initPhoneFormatting(root); } catch (err) { console.warn(err); } }
                        try { $(root).find('[data-offcanvas-close]').on('click', function(e){ e.preventDefault(); hideOffcanvas(); }); } catch(e){}
                    })
                    .fail(function() {
                        $('#prospectOffcanvasLoading').hide();
                        $('#prospectOffcanvasContent').html('<div class="alert alert-danger">No se pudo cargar el formulario de edición.</div>').show();
                    });
            });

        // Allow closing offcanvas when clicking outside the panel
        $(document).on('click', function(e) {
            if (!offcanvasEl) return;
            if (!offcanvasEl.classList.contains('show-offcanvas')) return;
            var $target = $(e.target);
            if ($target.closest('#prospectOffcanvas .offcanvas-body').length === 0 && $target.closest('.btn-offcanvas-detail, .btn-offcanvas-create').length === 0) {
                hideOffcanvas();
            }
        });
    });
</script>
<style>
    /* Simple offcanvas styles (Bootstrap 4 compatible fallback) */
    #prospectOffcanvas {
        position: fixed;
        top: 0;
        right: 0;
        height: 100%;
        /* Aumentado ligeramente para mayor comodidad en formularios */
        width: 560px;
        /* En pantallas pequeñas ocupará la mayor parte del ancho disponible */
        max-width: 90%;
        background: #fff;
        box-shadow: -10px 0 30px rgba(0,0,0,0.2);
        transform: translateX(100%);
        transition: transform 0.3s ease-in-out;
        z-index: 1050;
        overflow-y: auto;
    }
    #prospectOffcanvas.show-offcanvas {
        transform: translateX(0);
    }
    /* Header alignment fix */
    #prospectOffcanvas .offcanvas-header { 
        display:flex; 
        align-items:center; 
        justify-content:space-between; 
        padding:1rem; 
        border-bottom:1px solid #eee; 
    }
    #prospectOffcanvas .offcanvas-header .close {
        font-size: 2rem;
        font-weight: 300;
        line-height: 1;
        color: #000;
        opacity: 0.5;
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 0;
        margin: 0;
    }
    #prospectOffcanvas .offcanvas-header .close:hover {
        opacity: 0.75;
    }
    #prospectOffcanvas .offcanvas-body { padding:1rem; }

    /* Mejoras de responsividad para la tabla */
    @media (max-width: 768px) {
        .table-responsive {
            border: none;
        }
        
        #prospectsTable td {
            padding: 0.25rem !important;
            border: none;
            vertical-align: top;
        }
        
        #prospectsTable thead th {
            padding: 0.5rem !important;
            font-size: 0.7rem;
        }
        
        #prospectsTable .btn-sm {
            padding: 0.2rem 0.4rem;
            font-size: 0.7rem;
        }
        
        /* Mejorar el espaciado en móvil */
        .card-body {
            padding: 0 !important;
        }
        
        .table-responsive {
            padding: 0.5rem !important;
        }
        
        /* Compactar información en móvil */
        #prospectsTable h6 {
            font-size: 0.8rem !important;
            line-height: 1.2;
        }
        
        #prospectsTable .text-xs {
            font-size: 0.6rem !important;
            line-height: 1.1;
        }
        
        #prospectsTable small {
            font-size: 0.65rem !important;
        }
    }

    @media (max-width: 576px) {
        #prospectsTable {
            font-size: 0.75rem;
        }
        
        #prospectsTable td {
            padding: 0.15rem !important;
        }
        
        #prospectsTable .badge {
            font-size: 0.55rem !important;
            padding: 0.1rem 0.3rem !important;
        }
        
        #prospectsTable h6 {
            font-size: 0.75rem !important;
            margin-bottom: 0.1rem !important;
        }
        
        #prospectsTable .text-xs {
            font-size: 0.55rem !important;
        }
        
        #prospectsTable small {
            font-size: 0.6rem !important;
            line-height: 1.1;
        }
        
        /* Botón más compacto en móvil */
        #prospectsTable .btn-sm {
            padding: 0.15rem 0.3rem;
            font-size: 0.65rem;
        }
        
        /* Mejorar offcanvas en móvil */
        #prospectOffcanvas {
            width: 100% !important;
            max-width: 100% !important;
        }
        
        /* Tabla ultra compacta para pantallas muy pequeñas */
        .container-fluid {
            padding: 0.25rem !important;
        }
        
        .card {
            margin: 0.25rem;
        }
    }

    /* Pantallas extra pequeñas (Moto G55 y similares) */
    @media (max-width: 400px) {
        .table-responsive {
            padding: 0.25rem !important;
        }
        
        #prospectsTable td {
            padding: 0.1rem !important;
        }
        
        #prospectsTable h6 {
            font-size: 0.7rem !important;
        }
        
        #prospectsTable .text-xs {
            font-size: 0.5rem !important;
        }
        
        #prospectsTable small {
            font-size: 0.55rem !important;
        }
        
        #prospectsTable .btn-sm {
            padding: 0.1rem 0.25rem;
            font-size: 0.6rem;
        }
        
        /* Ocultar email en pantallas muy pequeñas */
        .d-block.d-sm-none {
            display: none !important;
        }
        
        .card-header h1 {
            font-size: 1.2rem !important;
        }
        
        .btn-success {
            font-size: 0.7rem !important;
            padding: 0.3rem 0.6rem !important;
        }
    }

    /* Mejoras para DataTables en móvil */
    @media (max-width: 768px) {
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            text-align: center;
            margin: 0.5rem 0;
        }
        
        .dataTables_wrapper .dataTables_filter input {
            width: 100% !important;
            margin-left: 0.5rem;
        }
        
        .dataTables_wrapper .dataTables_length select {
            margin: 0 0.25rem;
        }
    }
</style>
<?= $this->endSection(); ?>