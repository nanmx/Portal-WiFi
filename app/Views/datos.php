<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PortalFi - Usuarios Registrados</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            margin-top: 40px;
            margin-bottom: 40px;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }

        .badge-score {
            font-size: 0.85rem;
        }

        .pagination {
            justify-content: center;
        }

        .title-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .small-muted {
            font-size: 0.85rem;
            color: #6c757d;
        }

        /* Estilos para la visualización de datos */
        .data-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 8px;
            min-width: 200px;
        }

        .data-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 10px;
            border-bottom: 1px solid #e9ecef;
            font-size: 0.85rem;
        }

        .data-item:last-child {
            border-bottom: none;
        }

        .data-label {
            font-weight: 600;
            color: #495057;
            margin-right: 10px;
        }

        .data-value {
            color: #212529;
            word-break: break-word;
            text-align: right;
            flex: 1;
        }

        .data-value i {
            margin-right: 4px;
        }

        /* Filter buttons */
        .filter-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 8px 20px;
            border-radius: 20px;
            transition: all 0.3s;
        }
        
        .filter-btn.active {
            background-color: #dd4814;
            color: white;
        }
        
        .stats-cards {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            flex: 1;
            min-width: 150px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .toggle-email-btn {
            padding: 4px 10px;
            font-size: 0.75rem;
        }
        
        /* Modal styles */
        .modal-data-item {
            padding: 12px 0;
            border-bottom: 1px solid #dee2e6;
        }

        .modal-data-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
        }

        .modal-data-value {
            color: #212529;
            word-break: break-word;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .data-card {
                min-width: 180px;
            }
            
            .data-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .data-value {
                text-align: left;
                margin-top: 4px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    
    <div class="title-bar">
        <div>
            <h2 class="mb-0">
                <i class="fas fa-users"></i> Usuarios Registrados
            </h2>
            <div class="small-muted">
                <i class="fas fa-wifi"></i> PortalFi Data Viewer
            </div>
        </div>
        <div>
            <a href="<?= site_url('datos/exportCsv?filter=' . $currentFilter) ?>" class="btn btn-success btn-sm">
                <i class="fas fa-download"></i> Exportar CSV
            </a>
        </div>
    </div>
    
    <!-- Tarjetas de estadísticas -->
    <div class="stats-cards">
        <div class="stat-card">
            <i class="fas fa-users fa-2x text-primary"></i>
            <h3 class="mt-2"><?= $stats['total'] ?></h3>
            <p class="mb-0 text-muted">Total Usuarios</p>
        </div>
        <div class="stat-card">
            <i class="fas fa-check-circle fa-2x text-success"></i>
            <h3 class="mt-2"><?= $stats['valid'] ?></h3>
            <p class="mb-0 text-muted">Email Válido</p>
            <small><?= $stats['valid_percentage'] ?>%</small>
        </div>
        <div class="stat-card">
            <i class="fas fa-times-circle fa-2x text-danger"></i>
            <h3 class="mt-2"><?= $stats['invalid'] ?></h3>
            <p class="mb-0 text-muted">Email Inválido</p>
            <small><?= $stats['invalid_percentage'] ?>%</small>
        </div>
    </div>

    <!-- Filtros -->
    <div class="filter-buttons">
        <a href="?filter=valid" class="btn filter-btn <?= $currentFilter == 'valid' ? 'active btn-primary' : 'btn-outline-secondary' ?>">
            <i class="fas fa-check-circle"></i> Válidos
        </a>
        <a href="?filter=invalid" class="btn filter-btn <?= $currentFilter == 'invalid' ? 'active btn-primary' : 'btn-outline-secondary' ?>">
            <i class="fas fa-times-circle"></i> Inválidos
        </a>
        <a href="?filter=all" class="btn filter-btn <?= $currentFilter == 'all' ? 'active btn-primary' : 'btn-outline-secondary' ?>">
            <i class="fas fa-list"></i> Todos
        </a>
    </div>

    <div class="card p-4">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Identificador</th>
                        <th>Datos del Usuario</th>
                        <th>Logins</th>
                        <th>Último Login</th>
                        <th>Email Score</th>
                        <th>Estado Email</th>
                        <th>Dispositivo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($usuarios)): ?>
                    <?php foreach ($usuarios as $user): 
                        $decoded = json_decode($user['data'], true);
						//var_dump($user['is_valid_email']);
                        $isValid = isset($user['is_valid_email']) ? (int)$user['is_valid_email'] : 1;
                    ?>
                        <tr>
                            <td><?= esc($user['id']) ?></td>
                            <td>
                                <strong><?= esc($user['identifier']) ?></strong>
                                <span class="badge bg-primary">
                                    <?= esc($user['identifier_type']) ?>
                                </span>
                            </td>
                           
                            <td>
                                <!-- Vista previa de datos -->
                                <div class="data-card" data-bs-toggle="modal" data-bs-target="#dataModal<?= $user['id'] ?>" style="cursor: pointer;">
                                    <div class="data-item">
                                        <span class="data-label">
                                            <i class="fas fa-user"></i> Nombre:
                                        </span>
                                        <span class="data-value">
                                            <?= esc($decoded['name'] ?? 'N/A') ?>
                                        </span>
                                    </div>
                                    <div class="data-item">
                                        <span class="data-label">
                                            <i class="fas fa-envelope"></i> Email:
                                        </span>
                                        <span class="data-value">
                                            <?= esc($decoded['email'] ?? 'N/A') ?>
                                        </span>
                                    </div>
                                    <div class="data-item">
                                        <span class="data-label">
                                            <i class="fas fa-globe"></i> País:
                                        </span>
                                        <span class="data-value">
                                            <?php 
                                                $pais = $decoded['pais'] ?? 'N/A';
                                                $paisIcon = '';
                                                switch($pais) {
                                                    case 'MX':
                                                        $paisIcon = '🇲🇽 México';
                                                        break;
                                                    case 'USA':
                                                        $paisIcon = '🇺🇸 Estados Unidos';
                                                        break;
                                                    case 'Canada':
                                                        $paisIcon = '🇨🇦 Canadá';
                                                        break;
                                                    default:
                                                        $paisIcon = $pais;
                                                }
                                            ?>
                                            <?= esc($paisIcon) ?>
                                        </span>
                                    </div>
                                    <div class="data-item">
                                        <span class="data-label">
                                            <i class="fas fa-map-pin"></i> C.P.:
                                        </span>
                                        <span class="data-value">
                                            <?= esc($decoded['codigo_postal'] ?? 'N/A') ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Modal para ver datos completos -->
                                <div class="modal fade" id="dataModal<?= $user['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-user-circle"></i> Detalles del Usuario
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="modal-data-item">
                                                    <div class="modal-data-label">
                                                        <i class="fas fa-user"></i> Nombre completo:
                                                    </div>
                                                    <div class="modal-data-value">
                                                        <?= esc($decoded['name'] ?? 'N/A') ?>
                                                    </div>
                                                </div>
                                                <div class="modal-data-item">
                                                    <div class="modal-data-label">
                                                        <i class="fas fa-envelope"></i> Correo electrónico:
                                                    </div>
                                                    <div class="modal-data-value">
                                                        <?= esc($decoded['email'] ?? 'N/A') ?>
                                                    </div>
                                                </div>
                                                <div class="modal-data-item">
                                                    <div class="modal-data-label">
                                                        <i class="fas fa-globe"></i> País:
                                                    </div>
                                                    <div class="modal-data-value">
                                                        <?= esc($paisIcon) ?>
                                                    </div>
                                                </div>
                                                <div class="modal-data-item">
                                                    <div class="modal-data-label">
                                                        <i class="fas fa-map-pin"></i> Código Postal:
                                                    </div>
                                                    <div class="modal-data-value">
                                                        <?= esc($decoded['codigo_postal'] ?? 'N/A') ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                    <i class="fas fa-times"></i> Cerrar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                             </div>
                         </td>
                         
                         <td>
                            <span class="badge bg-secondary">
                                <i class="fas fa-sign-in-alt"></i> <?= esc($user['login_count']) ?>
                            </span>
                         </td>
                         <td>
                            <small><?= esc($user['last_login'] ?? '-') ?></small>
                         </td>
                         <td>
                            <?php
                                $score = (int) ($user['email_score'] ?? 0);
                                if ($score >= 80) {
                                    $badge = 'success';
                                    $icon = 'fas fa-check-circle';
                                } elseif ($score >= 50) {
                                    $badge = 'warning';
                                    $icon = 'fas fa-exclamation-triangle';
                                } else {
                                    $badge = 'danger';
                                    $icon = 'fas fa-times-circle';
                                }
                            ?>
							 <!-- Campos hidden para datos internos (no visibles) -->
                            <input type="hidden" id="site_<?= $user['id'] ?>" value="<?= esc($user['site'] ?? '') ?>">
                            <input type="hidden" id="client_mac_<?= $user['id'] ?>" value="<?= esc($user['client_mac'] ?? '') ?>">
                            <span class="badge bg-<?= $badge ?> badge-score">
                                <i class="<?= $icon ?>"></i> <?= $score ?>
                            </span>
                         </td>
                         <td>
                            <!-- CORRECCIÓN: Usar $isValid correctamente -->
                            <span class="badge <?= $isValid ? 'bg-success' : 'bg-danger' ?>" id="status-badge-<?= $user['id'] ?>">
                                <i class="fas <?= $isValid ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                                <?= $isValid ? 'Válido' : 'Inválido' ?>
                            </span>
                         </td>
                         <td>
                            <small><?= esc(substr($user['user_agent'] ?? '', 0, 50)) ?>...</small><br>
                            <code class="small"><?= esc($user['client_mac']) ?></code>
                         </td>
                         <td>
                            <button class="btn btn-sm btn-warning toggle-email" data-id="<?= $user['id'] ?>" data-status="<?= $isValid ?>">
                                <i class="fas fa-sync-alt"></i> Cambiar
                            </button>
                         </td>
                     </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center">
                            <i class="fas fa-database"></i> No hay registros disponibles.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN -->
        <div class="mt-4">
            <?= $pager->links() ?>
        </div>
    </div>
</div>

<!-- Bootstrap JS y Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Función para cambiar estado de email
async function toggleEmailStatus(userId, currentStatus) {
    // Obtener los datos de los campos hidden
    const site = document.getElementById(`site_${userId}`).value;
    const clientMac = document.getElementById(`client_mac_${userId}`).value;
    
    // Mostrar loading en el botón
    const button = document.querySelector(`.toggle-email[data-id="${userId}"]`);
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
    button.disabled = true;
    
    try {
        const response = await fetch('<?= site_url('datos/toggleEmailStatus') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `user_id=${userId}&site=${encodeURIComponent(site)}&client_mac=${encodeURIComponent(clientMac)}`
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Actualizar el badge
            const badge = document.getElementById(`status-badge-${userId}`);
            const newStatus = result.new_status;
            
            if (newStatus == 1) {
                badge.innerHTML = '<i class="fas fa-check-circle"></i> Válido';
                badge.className = 'badge bg-success';
            } else {
                badge.innerHTML = '<i class="fas fa-times-circle"></i> Inválido';
                badge.className = 'badge bg-danger';
            }
            
            // Actualizar estadísticas si se proporcionan
            if (result.stats) {
                updateStats(result.stats);
            }
            
            // Mostrar notificación de éxito con detalles de Omada si existen
            let message = 'El estado del email ha sido cambiado exitosamente.';
            if (result.omada_action) {
                message += '\n\nOmada: ' + result.omada_action;
            }
            
            Swal.fire({
                icon: 'success',
                title: '¡Actualizado!',
                text: message,
                timer: 3000,
                showConfirmButton: false
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'No se pudo actualizar el estado'
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'No se pudo conectar con el servidor'
        });
    } finally {
        button.innerHTML = originalText;
        button.disabled = false;
    }
}

// Función para actualizar estadísticas
function updateStats(stats) {
    const statCards = document.querySelectorAll('.stat-card');
    if (statCards.length >= 3) {
        statCards[0].querySelector('h3').textContent = stats.total;
        statCards[1].querySelector('h3').textContent = stats.valid;
        statCards[1].querySelector('small').textContent = stats.valid_percentage + '%';
        statCards[2].querySelector('h3').textContent = stats.invalid;
        statCards[2].querySelector('small').textContent = stats.invalid_percentage + '%';
    }
}

// Event listeners para los botones de toggle
document.querySelectorAll('.toggle-email').forEach(button => {
    button.addEventListener('click', function() {
        const userId = this.dataset.id;
        const currentStatus = this.dataset.status;
        toggleEmailStatus(userId, currentStatus);
    });
});

// Inicializar tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
});

// Efecto de zoom en tarjetas de datos
document.querySelectorAll('.data-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.02)';
        this.style.transition = 'transform 0.2s';
    });
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
    });
});
</script>
</body>
</html>