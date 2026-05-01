<?php

namespace App\Libraries;

class OmadaApi
{
    protected string $baseUrl;
    protected string $username;
    protected string $password;
    protected string $cookieFile;

    public function __construct()
    {
        $this->baseUrl = "https://192.168.10.75/c3af5c40edbb9af94b116d9cdd4e6fda";
        $this->username = "it";
        $this->password = "Vallar7@198";

        $cookieDir = WRITEPATH . "omada/";
        if (!is_dir($cookieDir)) {
            mkdir($cookieDir, 0755, true);
        }

        $this->cookieFile = $cookieDir . "omada_cookie.txt";
    }

    /**
     * ==========================
     * CURL BASE
     * ==========================
     */
    private function request(string $endpoint, string $method = "POST", array $payload = [], ?string $token = null)
    {
        $url = $this->baseUrl . $endpoint;

        $headers = [
            "Content-Type: application/json",
            "Accept: application/json",
        ];

        if ($token) {
            $headers[] = "Csrf-Token: {$token}";
        }

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => !empty($payload) ? json_encode($payload) : null,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => false,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            log_message("error", "Omada CURL Error: " . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return [
            "http_code" => $httpCode,
            "raw" => $response,
            "decoded" => json_decode($response),
        ];
    }

    /**
     * ==========================
     * LOGIN
     * ==========================
     */
    public function login(): ?string
    {
        $response = $this->request("/api/v2/hotspot/login", "POST", [
            "name" => $this->username,
            "password" => $this->password,
        ]);

        if (
            isset($response["decoded"]->result->token) &&
            $response["decoded"]->errorCode == 0
        ) {
            return $response["decoded"]->result->token;
        }

        log_message("error", "Omada Login Failed: " . $response["raw"]);

        return null;
    }

    /**
     * ==========================
     * AUTORIZAR CLIENTE
     * ==========================
     */
    public function authorizeClient(array $clientData)
    {
        $token = $this->login();

        if (!$token) {
            return [
                "success" => false,
                "error" => "Omada login failed",
            ];
        }

        $payload = [
            "clientMac" => $clientData["clientMac"],
            "apMac"     => $clientData["apMac"],
            "ssidName"  => $clientData["ssidName"],
            "radioId"   => $clientData["radioId"],
            "site"      => $clientData["site"],
            "time"      => $clientData["time"] ?? 3600000,
            "authType"  => 4,
        ];

        $response = $this->request(
            "/api/v2/hotspot/extPortal/auth",
            "POST",
            $payload,
            $token
        );

        if (
            isset($response["decoded"]->errorCode) &&
            $response["decoded"]->errorCode == 0
        ) {
            return [
                "success" => true,
                "data" => $response["decoded"],
            ];
        }

        return [
            "success" => false,
            "error" => $response["raw"],
        ];
    }

    /**
     * ==========================
     * DESAUTORIZAR CLIENTE
     * ==========================
     */
    public function unauthorizeClient(string $site, string $clientMac)
    {
        $token = $this->login();

        if (!$token) {
            return [
                "success" => false,
                "error" => "Omada login failed",
            ];
        }

        $payload = [
            "site" => $site,
            "clientMac" => $clientMac,
        ];

        $response = $this->request(
            "/api/v2/hotspot/extPortal/unauthorize",
            "POST",
            $payload,
            $token
        );

        return [
            "success" =>
                isset($response["decoded"]->errorCode) &&
                $response["decoded"]->errorCode == 0,
            "response" => $response,
        ];
    }

    /**
     * ==========================
     * DESCONECTAR / BORRAR CLIENTE
     * ==========================
     */
    public function deleteClient(string $site, string $clientMac)
    {
        $token = $this->login();

        if (!$token) {
            return [
                "success" => false,
                "error" => "Omada login failed",
            ];
        }

        $payload = [
            "site" => $site,
            "clientMac" => $clientMac,
        ];

        $response = $this->request(
            "/api/v2/hotspot/extPortal/delete",
            "POST",
            $payload,
            $token
        );

        return [
            "success" =>
                isset($response["decoded"]->errorCode) &&
                $response["decoded"]->errorCode == 0,
            "response" => $response,
        ];
    }
	    /**
     * ==========================
     * BLOQUEAR CLIENTE (BLACKLIST)
     * ==========================
     * Esto agrega la MAC a blacklist del sitio.
     * Útil para impedir futuras conexiones automáticamente.
     */
    public function blockClient(string $site, string $clientMac, string $name = "Blocked Client")
    {
        $token = $this->login();

        if (!$token) {
            return [
                "success" => false,
                "error" => "Omada login failed",
            ];
        }

        /**
         * Algunos controladores Omada usan blacklist/add
         * Si tu versión cambia, puede ser:
         * /api/v2/sites/{site}/blacklist
         * o hotspot/blacklist/add
         *
         * Este formato suele funcionar en v5.x
         */
        $payload = [
            "site" => $site,
            "mac" => $clientMac,
            "name" => $name,
        ];

        $response = $this->request(
            "/api/v2/hotspot/blacklist/add",
            "POST",
            $payload,
            $token
        );

        /**
         * Respuesta exitosa esperada:
         * errorCode = 0
         */
        if (
            isset($response["decoded"]->errorCode) &&
            $response["decoded"]->errorCode == 0
        ) {
            return [
                "success" => true,
                "message" => "Client blocked successfully",
                "response" => $response["decoded"],
            ];
        }

        return [
            "success" => false,
            "error" => "Block failed",
            "response" => $response,
        ];
    }
	/**
	 * ==========================
	 * VERIFICAR ESTADO DEL CLIENTE
	 * ==========================
	 */
	public function checkClientStatus(string $site, string $clientMac)
	{
		$token = $this->login();
		
		if (!$token) {
			return [
				"success" => false,
				"error" => "Omada login failed",
			];
		}
		
		$response = $this->request(
			"/api/v2/hotspot/clients?site={$site}&clientMac={$clientMac}",
			"GET",
			[],
			$token
		);
		
		return [
			"success" => isset($response["decoded"]->errorCode) && $response["decoded"]->errorCode == 0,
			"data" => $response["decoded"] ?? null
		];
	}
}