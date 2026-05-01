<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Dato;
use App\Libraries\OmadaApi;

class Datos extends BaseController
{
    protected $omadaApi;
    
    public function __construct()
    {
        $this->omadaApi = new OmadaApi();
    }
    
    public function index()
    {
        $datoModel = new Dato();
        $perPage = 20;
        
        // Obtener el filtro de la URL (por defecto: valid)
        $filter = $this->request->getGet('filter') ?? 'valid';
        
        // Obtener usuarios según el filtro
        if ($filter === 'valid') {
            $usuarios = $datoModel->getUsersByEmailStatus(1, $perPage);
        } elseif ($filter === 'invalid') {
            $usuarios = $datoModel->getUsersByEmailStatus(0, $perPage);
        } else {
            $usuarios = $datoModel->getPaginatedUsers($perPage);
        }
        
        // Obtener estadísticas
        $stats = $datoModel->getEmailStatusStats();
        
        $data = [
            'usuarios' => $usuarios,
            'pager'    => $datoModel->getPager(),
            'currentFilter' => $filter,
            'stats' => $stats
        ];
        
        return view('datos', $data);
    }
    
    /**
     * Cambia el estado de is_valid_email via AJAX
     * Si cambia de 1 a 0 (válido a inválido), desautoriza y elimina al cliente de Omada
     */
    public function toggleEmailStatus()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Solicitud no válida'
            ]);
        }
        
        $userId = $this->request->getPost('user_id');
        $datoModel = new Dato();
        
        // Obtener el estado actual antes de cambiar
        $currentStatus = $datoModel->getValidEmailStatus($userId);
        
        if ($currentStatus === null) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ]);
        }
        
        // Cambiar el estado (toggle)
        $newStatus = $currentStatus == 1 ? 0 : 1;
        
        // Si está cambiando de VÁLIDO (1) a INVÁLIDO (0), desautorizar y eliminar de Omada
        $omadaResult = null;
        if ($currentStatus == 1 && $newStatus == 0) {
            $omadaResult = $this->unauthorizeAndDeleteFromOmada($userId, $datoModel);
        }
        
        // Actualizar el estado en la base de datos
        if ($datoModel->setValidEmailStatus($userId, $newStatus)) {
            // Si la operación de Omada falló, registrar el error pero no detener el proceso
            if ($omadaResult && !$omadaResult['success']) {
                log_message('error', 'Omada operation failed for user ' . $userId . ': ' . ($omadaResult['error'] ?? 'Unknown error'));
            }
            
            $stats = $datoModel->getEmailStatusStats();
            
            return $this->response->setJSON([
                'success' => true,
                'new_status' => $newStatus,
                'message' => 'Estado actualizado correctamente',
                'stats' => $stats,
                'omada_action' => $omadaResult ? ($omadaResult['success'] ? 'Cliente desautorizado y eliminado de Omada' : 'Error en operación de Omada') : null
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al actualizar el estado'
            ]);
        }
    }
    
    /**
     * Cambia el estado de múltiples usuarios
     */
    public function bulkUpdateEmailStatus()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Solicitud no válida'
            ]);
        }
        
        $userIds = $this->request->getPost('user_ids');
        $status = (int)$this->request->getPost('status'); // 0 o 1
        $datoModel = new Dato();
        
        $results = [
            'success' => 0,
            'failed' => 0,
            'omada_success' => 0,
            'omada_failed' => 0,
            'details' => []
        ];
        
        foreach ($userIds as $userId) {
            // Obtener estado actual
            $currentStatus = $datoModel->getValidEmailStatus($userId);
            
            if ($currentStatus === null) {
                $results['failed']++;
                $results['details'][] = [
                    'user_id' => $userId,
                    'status' => 'failed',
                    'reason' => 'Usuario no encontrado'
                ];
                continue;
            }
            
            // Solo procesar si el estado actual es diferente al nuevo
            if ($currentStatus != $status) {
                // Si está cambiando de VÁLIDO a INVÁLIDO, desautorizar de Omada
                $omadaResult = null;
                if ($currentStatus == 1 && $status == 0) {
                    $omadaResult = $this->unauthorizeAndDeleteFromOmada($userId, $datoModel);
                    if ($omadaResult['success']) {
                        $results['omada_success']++;
                    } else {
                        $results['omada_failed']++;
                        log_message('error', 'Omada operation failed for user ' . $userId . ': ' . ($omadaResult['error'] ?? 'Unknown error'));
                    }
                }
                
                // Actualizar en BD
                if ($datoModel->setValidEmailStatus($userId, $status)) {
                    $results['success']++;
                    $results['details'][] = [
                        'user_id' => $userId,
                        'status' => 'success',
                        'omada' => $omadaResult ? ($omadaResult['success'] ? 'ok' : 'failed') : 'not_needed'
                    ];
                } else {
                    $results['failed']++;
                    $results['details'][] = [
                        'user_id' => $userId,
                        'status' => 'failed',
                        'reason' => 'Error en BD'
                    ];
                }
            } else {
                $results['details'][] = [
                    'user_id' => $userId,
                    'status' => 'skipped',
                    'reason' => 'Ya estaba en el estado solicitado'
                ];
            }
        }
        
        $stats = $datoModel->getEmailStatusStats();
        
        return $this->response->setJSON([
            'success' => true,
            'results' => $results,
            'stats' => $stats,
            'message' => "{$results['success']} usuarios actualizados, {$results['failed']} fallaron"
        ]);
    }
    
    /**
     * Desautoriza y elimina un cliente de Omada
     * 
     * @param int $userId ID del usuario en la BD
     * @param Dato $datoModel Modelo de datos
     * @return array Resultado de la operación
     */
    private function unauthorizeAndDeleteFromOmada($userId, $datoModel)
    {
        // Obtener datos del usuario
        $user = $datoModel->find($userId);
        
        if (!$user) {
            return [
                'success' => false,
                'error' => 'Usuario no encontrado'
            ];
        }
        
        // Verificar que tengamos los datos necesarios para Omada
        $site = $user['site'];
        $clientMac = $user['client_mac'];
        
        if (empty($site) || empty($clientMac)) {
            return [
                'success' => false,
                'error' => 'Faltan datos de sitio o MAC para operación Omada',
                'site' => $site,
                'client_mac' => $clientMac
            ];
        }
        
        $results = [
            'success' => true,
            'unauthorize' => null,
            'delete' => null
        ];
        
        // 1. Desautorizar al cliente
        $unauthorizeResult = $this->omadaApi->unauthorizeClient($site, $clientMac);
        $results['unauthorize'] = $unauthorizeResult;
        
        if (!$unauthorizeResult['success']) {
            log_message('warning', 'Failed to unauthorize client ' . $clientMac . ' on site ' . $site . ': ' . json_encode($unauthorizeResult));
            // No detenemos el proceso aunque falle la desautorización
        }
        
        // 2. Eliminar al cliente
        $deleteResult = $this->omadaApi->deleteClient($site, $clientMac);
        $results['delete'] = $deleteResult;
        
        if (!$deleteResult['success']) {
            log_message('warning', 'Failed to delete client ' . $clientMac . ' on site ' . $site . ': ' . json_encode($deleteResult));
            $results['success'] = false;
            $results['error'] = 'Error al eliminar cliente de Omada';
        }
        
        // Registrar en log la operación
        log_message('info', 'Omada operations for user ' . $userId . ' (MAC: ' . $clientMac . '): Unauthorize=' . 
                   ($unauthorizeResult['success'] ? 'OK' : 'FAIL') . ', Delete=' . 
                   ($deleteResult['success'] ? 'OK' : 'FAIL'));
        
        return $results;
    }
    
    /**
     * Exportar usuarios a CSV
     */
    public function exportCsv()
    {
        $datoModel = new Dato();
        $filter = $this->request->getGet('filter') ?? 'all';
        
        if ($filter === 'valid') {
            $usuarios = $datoModel->getUsersByEmailStatus(1, 10000);
        } elseif ($filter === 'invalid') {
            $usuarios = $datoModel->getUsersByEmailStatus(0, 10000);
        } else {
            $usuarios = $datoModel->findAll(10000);
        }
        
        $filename = 'usuarios_' . date('Y-m-d_H-i') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Encabezados
        fputcsv($output, ['ID', 'Identificador', 'Tipo', 'Nombre', 'Email', 'País', 'CP', 'Logins', 'Último Login', 'Email Score', 'Email Válido', 'MAC', 'Site', 'User Agent']);
        
        foreach ($usuarios as $user) {
            $decoded = json_decode($user['data'], true);
            
            fputcsv($output, [
                $user['id'],
                $user['identifier'],
                $user['identifier_type'],
                $decoded['name'] ?? 'N/A',
                $decoded['email'] ?? 'N/A',
                $decoded['pais'] ?? 'N/A',
                $decoded['codigo_postal'] ?? 'N/A',
                $user['login_count'],
                $user['last_login'],
                $user['email_score'],
                $user['is_valid_email'] == 1 ? 'Sí' : 'No',
                $user['client_mac'],
                $user['site'],
                $user['user_agent']
            ]);
        }
        
        fclose($output);
        exit();
    }
    
    /**
     * Función adicional: Sincronizar un usuario específico con Omada
     * Útil para forzar la desautorización manual
     */
    public function syncWithOmada($userId)
    {
        $datoModel = new Dato();
        
        // Verificar que el usuario existe y tiene email inválido
        $user = $datoModel->find($userId);
        
        if (!$user) {
            return redirect()->back()->with('error', 'Usuario no encontrado');
        }
        
        // Solo procesar si el email es inválido
        if ($user['is_valid_email'] == 0) {
            $result = $this->unauthorizeAndDeleteFromOmada($userId, $datoModel);
            
            if ($result['success']) {
                return redirect()->back()->with('success', 'Usuario sincronizado con Omada correctamente');
            } else {
                return redirect()->back()->with('error', 'Error al sincronizar: ' . ($result['error'] ?? 'Desconocido'));
            }
        } else {
            return redirect()->back()->with('info', 'El usuario tiene email válido, no se requiere acción en Omada');
        }
    }
}