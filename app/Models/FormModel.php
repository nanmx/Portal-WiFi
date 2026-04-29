<?php namespace App\Models;
use CodeIgniter\Model;
class FormModel extends Model
{
    protected $table = "users";
    protected $primaryKey = "id";
    protected $useAutoIncrement = true;
    protected $returnType = "array";
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
        'email_score'
    ];
    protected $useTimestamps = false;
    // ================================
    // INSERTAR USUARIO
    // ================================
    public function saveUser($formData, $portalData, $request)
    {
		 $identifier = $formData['email'] ?? null;
        $identifierType = 'email';

        if (!$identifier) {
            return false; // obligatorio
        }
		 //  Buscar si ya existe
        $existing = $this->where('identifier', $identifier)
                         ->where('identifier_type', $identifierType)
                         ->first();
		$data = [
            'identifier'      => $identifier,
            'identifier_type' => $identifierType,
            'data'            => json_encode($formData),
            'client_mac'      => $portalData['clientMac'] ?? null,
            'ap_mac'          => $portalData['apMac'] ?? null,
            'gateway_mac'     => $portalData['gatewayMac'] ?? null,
            'ssid'            => $portalData['ssidName'] ?? null,
            'site'            => $portalData['site'] ?? null,
            'ip_address'      => $request->getIPAddress(),
            'user_agent'      => $request->getUserAgent()->getAgentString(),
            'is_valid_email'  => $this->isValidEmail($identifier),
            'email_score'     => $this->emailScore($identifier),
            'last_login'      => date('Y-m-d H:i:s')
        ];
        if ($existing) {
            // 🔁 UPDATE (usuario ya existe)
            return $this->update($existing['id'], [
                'login_count' => $existing['login_count'] + 1,
                'last_login'  => $data['last_login'],
                'client_mac'  => $data['client_mac'],
                'ip_address'  => $data['ip_address']
            ]);
        } else {
            // 🆕 INSERT
            $data['login_count'] = 1;
            return $this->insert($data);
        }
    }
    // ================================
    // VALIDACIÓN SIMPLE DE EMAIL
    // ================================
    private function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? 1 : 0;
    }
    // ================================
    // SCORE DE EMAIL (anti fake básico)
    // ================================
    private function emailScore($email)
    {
        $score = 100;
        $fakeDomains = [
            "mailinator.com",
            "tempmail.com",
            "10minutemail.com",
            "fake.com",
        ];
        $domain = substr(strrchr($email, "@"), 1);
        if (in_array($domain, $fakeDomains)) {
            $score = 10;
        }
        if (strlen($email) < 6) {
            $score -= 30;
        }
        return max($score, 0);
    }
    // ================================
    // BUSCAR POR EMAIL
    // ================================
    public function findByEmail($email)
    {
        return $this->where("email", $email)->first();
    }
    // ================================
    // CONTAR USUARIOS
    // ================================
    public function countUsers()
    {
        return $this->countAllResults();
    }
    // ================================
    // ÚLTIMOS REGISTROS
    // ================================
    public function getRecentUsers($limit = 10)
    {
        return $this->orderBy("created_at", "DESC")
            ->limit($limit)
            ->find();
    }
}
