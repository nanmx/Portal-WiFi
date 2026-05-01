<?php

namespace App\Models;

use CodeIgniter\Model;

class Dato extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'identifier',
        'identifier_type',
        'data',
        'client_mac',
        'ap_mac',
        'gateway_mac',
        'ssid',
        'site',
        'ip_address',
        'user_agent',
        'login_count',
        'last_login',
        'is_valid_email',
        'email_score',
        'created_at'
    ];

    /**
     * Obtiene usuarios paginados
     */
    public function getPaginatedUsers($perPage = 20)
    {
        return $this->select('id, identifier, identifier_type, data, client_mac, ssid, site, login_count, last_login, user_agent, email_score,is_valid_email')
                    ->orderBy('last_login', 'DESC')
                    ->paginate($perPage);
    }

    /**
     * Pager para la vista
     */
    public function getPager()
    {
        return $this->pager;
    }

    /**
     * Cambia el estado de is_valid_email (toggle)
     * Si es 1 lo cambia a 0, si es 0 lo cambia a 1
     * 
     * @param int $userId ID del usuario
     * @return bool True si se actualizó correctamente, False si hubo error
     */
    public function toggleValidEmail($userId)
    {
        // Validar que el ID sea numérico
        if (!is_numeric($userId)) {
            return false;
        }

        // Obtener el estado actual
        $user = $this->find($userId);
        
        if (!$user) {
            return false;
        }
        
        // Cambiar el estado (toggle)
        $newStatus = $user['is_valid_email'] == 1 ? 0 : 1;
        
        // Actualizar
        return $this->update($userId, ['is_valid_email' => $newStatus]);
    }

    /**
     * Cambia is_valid_email a un valor específico
     * 
     * @param int $userId ID del usuario
     * @param int $status 0 o 1
     * @return bool True si se actualizó correctamente
     */
    public function setValidEmailStatus($userId, $status)
    {
        // Validar el estado
        $status = $status == 1 ? 1 : 0;
        
        // Validar que el ID sea numérico
        if (!is_numeric($userId)) {
            return false;
        }
        
        return $this->update($userId, ['is_valid_email' => $status]);
    }

    /**
     * Cambia el estado de is_valid_email para múltiples usuarios
     * 
     * @param array $userIds Array de IDs de usuarios
     * @param int $status 0 o 1
     * @return bool True si todos se actualizaron correctamente
     */
    public function bulkSetValidEmailStatus($userIds, $status)
    {
        // Validar que sea un array
        if (!is_array($userIds) || empty($userIds)) {
            return false;
        }
        
        // Validar el estado
        $status = $status == 1 ? 1 : 0;
        
        // Filtrar solo IDs numéricos
        $userIds = array_filter($userIds, 'is_numeric');
        
        if (empty($userIds)) {
            return false;
        }
        
        // Actualizar en lote
        return $this->whereIn('id', $userIds)
                    ->set(['is_valid_email' => $status])
                    ->update();
    }

    /**
     * Obtiene el estado actual de is_valid_email
     * 
     * @param int $userId ID del usuario
     * @return int|null Estado (0 o 1) o null si no existe
     */
    public function getValidEmailStatus($userId)
    {
        if (!is_numeric($userId)) {
            return null;
        }
        
        $user = $this->select('is_valid_email')->find($userId);
        
        return $user ? $user['is_valid_email'] : null;
    }

    /**
     * Obtiene usuarios por estado de email
     * 
     * @param int $status 0 o 1
     * @param int $perPage Cantidad por página
     * @return array Usuarios filtrados
     */
    public function getUsersByEmailStatus($status, $perPage = 20)
    {
        $status = $status == 1 ? 1 : 0;
        
        return $this->select('id, identifier, identifier_type, data, client_mac, ssid, site, login_count, last_login, user_agent, email_score,is_valid_email')
                    ->where('is_valid_email', $status)
                    ->orderBy('last_login', 'DESC')
                    ->paginate($perPage);
    }

    /**
     * Estadísticas de emails válidos vs inválidos
     * 
     * @return array Estadísticas
     */
    public function getEmailStatusStats()
    {
        $total = $this->countAll();
        
        $validCount = $this->where('is_valid_email', 1)->countAllResults();
        $invalidCount = $this->where('is_valid_email', 0)->countAllResults();
        
        return [
            'total' => $total,
            'valid' => $validCount,
            'invalid' => $invalidCount,
            'valid_percentage' => $total > 0 ? round(($validCount / $total) * 100, 2) : 0,
            'invalid_percentage' => $total > 0 ? round(($invalidCount / $total) * 100, 2) : 0
        ];
    }
}