<?php namespace App\Controllers;
use CodeIgniter\Controller;
use App\Models\FormModel;
class FormController extends Controller
{
    protected $session;
    function __construct()
    {
        $this->session = \Config\Services::session();
    }
    public function index()
    {
        helper("form"); // Guardar parámetros GET que manda Omada
        $this->saveGetParameters(); //dd($this->request->getGet()); //dd($this->session->get());
        return view("portal_form", ["paises" => $this->getPaises()]);
    }
    private function saveGetParameters()
    {
        $params = [
            "clientMac",
            "apMac",
            "gatewayMac",
            "ssidName",
            "radioId",
            "vid",
            "site",
            "redirectUrl",
        ];
        $sessionData = [];
        foreach ($params as $param) {
            $value = $this->request->getGet($param);
            if ($value !== null) {
                $sessionData[$param] = $value;
            }
        }
        $this->session->set($sessionData);
    }
    public function procesar()
    {
        helper("form");
        $rules = [
            "name" => "required|min_length[3]|max_length[100]",
            "email" => "required|valid_email",
            "pais" => "required",
            "codigo_postal" => "required|min_length[3]|max_length[10]",
        ];
        if (!$this->validate($rules)) {
            return $this->showFormWithError("Please correct the form errors.");
        }
        // Guardar datos (aquí puedes meter BD después)
        $formData =[
            "name" => $this->request->getPost("name"),
            "email" => $this->request->getPost("email"),
            "pais" => $this->request->getPost("pais"),
            "codigo_postal" => $this->request->getPost("codigo_postal"),
        ];
        $portalData = [
            "clientMac" => $this->request->getPost("clientMac"),
            "apMac" => $this->request->getPost("apMac"),
            "gatewayMac" => $this->request->getPost("gatewayMac"),
            "ssidName" => $this->request->getPost("ssidName"),
            "radioId" => $this->request->getPost("radioId"),
            "vid" => $this->request->getPost("vid"),
            "site" => $this->request->getPost("site"),
            "redirectUrl" => $this->request->getPost("redirectUrl"),
        ];
        $this->session->set("form_data", $formData);
        $this->session->set($portalData); //dd($formData); //dd($this->request); 
		 if (!$portalData['clientMac']) {
            return $this->showFormWithError("Session lost. Please reconnect to WiFi.");
        }
		//dd($this->session->get());
        $model = new FormModel();
        $model->saveUser($formData, $portalData, $this->request); // Autenticación Omada
        $result = $this->processOmadaAuth();
        if ($result["success"]) {
            return redirect()->to($result["redirectUrl"]);
        }
        return $this->showFormWithError($result["error"]);
    }
    private function processOmadaAuth()
    {
        //dd($this->session->get()); // Validar que tenemos datos mínimos
        if (!session("clientMac")) {
            return [
                "success" => false,
                "error" => "Missing client information",
            ];
        }
        $token = $this->omadaLogin();
        if (!$token) {
            return ["success" => false, "error" => "Omada login failed"];
        }
        $auth = $this->omadaAuthorize($token);
		
        if (!$auth || $auth->errorCode != 0) {
            return [
                "success" => false,
                "error" =>
                    "Authorization failed: " . ($auth->errorCode ?? "Unknown"),
            ];
        }
        return [
            "success" => true,
            "redirectUrl" =>
                session("redirectUrl") ?? "https://casacupula.com/events",
        ];
    }
    private function omadaLogin()
    {
        $url =
            "https://192.168.10.75/c3af5c40edbb9af94b116d9cdd4e6fda/api/v2/hotspot/login";
        $data = json_encode([
            "name" => "it",
            "password" => "Vallar7@198",
        ]);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Accept: application/json",
            ],
            CURLOPT_COOKIEJAR => WRITEPATH . "omada/omada_cookie.txt",
            CURLOPT_COOKIEFILE => WRITEPATH . "omada/omada_cookie.txt",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        $response = curl_exec($ch);
        if ($response === false) {
            log_message("error", "Omada Login CURL Error: " . curl_error($ch));
        }
        curl_close($ch);
        $res = json_decode($response);
		
	//	dd($res);
		if ($res && isset($res->result->token)) {
		
			return $res->result->token;
		}
		return null;
		
    }
    private function omadaAuthorize($token)
    {
        $url =
            "https://192.168.10.75/c3af5c40edbb9af94b116d9cdd4e6fda/api/v2/hotspot/extPortal/auth"; // Detectar si es EAP o Gateway automáticamente
         $payload = [
            "clientMac" => $this->session->get('clientMac'),
            "apMac"     => $this->session->get('apMac'),
            "ssidName"  => $this->session->get('ssidName'),
            "radioId"   => $this->session->get('radioId'),
            "site"      => $this->session->get('site'),
            "time"      => 3600000, // 1 hora
            "authType"  => 4
        ];
		//dd($payload);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Accept: application/json",
                "Csrf-Token: " . $token,
            ],
            CURLOPT_COOKIEJAR => WRITEPATH . "omada/omada_cookie.txt",
            CURLOPT_COOKIEFILE => WRITEPATH . "omada/omada_cookie.txt",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        $response = curl_exec($ch);
        if ($response === false) {
            log_message("error", "Omada Auth CURL Error: " . curl_error($ch));
        }
        curl_close($ch);
//dd($response);
       return json_decode($response);
	   
    }
    private function showFormWithError($mensaje)
    {
        return view("portal_form", [
            "paises" => $this->getPaises(),
            "mensaje" => $mensaje,
            "tipo_mensaje" => "error",
        ]);
    }
    private function getPaises()
    {
        return [
            "" => "Selecciona un país",
            "MX" => "México",
            "USA" => "Estados Unidos",
            "Canada" => "Canadá",
            "Other" => "Otro",
        ];
    }
}
