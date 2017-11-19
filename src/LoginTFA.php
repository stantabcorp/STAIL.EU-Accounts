<?php 

    namespace STAILEUAccounts;

    class LoginTFA{

        private $stail;
        private $token;

        public function __construct($stail, $tfa_token){
            if(!($stail instanceof \STAILEUAccounts\STAILEUAccounts)){
                throw new Exception("Incorrect first parameter, it MUST BE an instance of '\STAILEUAccounts\STAILEUAccounts'", 1);
            }
            $this->stail = $stail;
            $this->token = $tfa_token;
        }

        public function sendTFACode($code){
            $client = new \GuzzleHttp\Client();
            $res = $client->request('POST', $this->stail->getApiUrl()."/login/tfa", [
                'form_params' => [
                    "token" => $this->token,
                    "code" => $code,
                    "site_key" => $this->stail->getPrivateKey(),
                ],
                'http_errors' => false,
            ]);
            $json = json_decode($res->getBody(), true);
            if($res->getStatusCode() == 200){
                if($json['success']){
                    return $json['c-sa'];
                }else{
                    return new \STAILEUAccounts\Error($res->getStatusCode(), $json['error']);
                }
            }else{
                return new \STAILEUAccounts\Error($res->getStatusCode(), $json['error']);
            }
        }

    }