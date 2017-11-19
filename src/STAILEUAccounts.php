<?php 

    namespace STAILEUAccounts;

    class STAILEUAccounts{

        private $private_key;
        private $public_key;
        private $cache  = false;
        private $api    = "http://localhost:8080/staileu-accounts/api/public";

        public function __construct($private_key, $public_key, $cache = false){
            if($cache != false){
                if(!($cache instanceof \STAILEUAccounts\Cache)){
                    throw new Exception("Incorrect third parameter, it MUST BE an instance of '\STAILEUAccounts\Cache' or 'false'", 1);
                }
            }
            $this->cache        = $cache;
            $this->private_key  = $private_key;
            $this->public_key   = $public_key;
        }

        public function getPrivateKey(){
            return $this->private_key;
        }

        public function getApiUrl(){
            return $this->api;
        }

        public function getPublicKey(){
            return $this->public_key;
        }

        public function login($username, $password){
            $client = new \GuzzleHttp\Client();
            $res = $client->request('POST', $this->api."/login", [
                'form_params' => [
                    "username" => $username,
                    "password" => md5($password),
                    "site_key" => $this->private_key,
                ],
                'http_errors' => false,
            ]);
            $json = json_decode($res->getBody(), true);
            if($res->getStatusCode() == 200){
                if($json['success']){
                    return new \STAILEUAccounts\Login(((isset($json['c-sa'])) ? $json['c-sa'] : null), $json['tfa'], ((isset($json['tfa_token'])) ? $json['tfa_token'] : null));
                }else{
                    return new \STAILEUAccounts\Error($res->getStatusCode(), $json['error']);
                }
            }else{
                return new \STAILEUAccounts\Error($res->getStatusCode(), $json['error']);
            }
        }

        public function register($username, $password, $email = null, $phone = null, $ip){
            $d = [
                "username" => $username,
                "password" => md5($password),
                "ip" => $ip,
                "site_key" => $this->private_key,
            ];
            if($email != null){
                $d['email'] = $email;
            }
            if($phone != null){
                $d['phone'] = $phone;
            }
            $client = new \GuzzleHttp\Client();
            $res = $client->request('POST', $this->api."/register", [
                'form_params' => $d,
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

        public function logout($CSA){
            $client = new \GuzzleHttp\Client();
            $res = $client->request('POST', $this->api."/logout", [
                'form_params' => [
                    'c-sa' => $CSA,
                ],
                'http_errors' => false,
            ]);
            return true;
        }

        public function loginForm($redirection){
            return "https://accounts.stail.eu/login?key=".$this->public_key."&redir=".$redirection;
        }

        public function registerForm($redirection){
            return "https://accounts.stail.eu/register?key=".$this->public_key."&redir=".$redirection;
        }

        public function forgotForm($redirection){
            return "https://accounts.stail.eu/forgot?key=".$this->public_key."&redir=".$redirection;
        }

        public function getUUID($username){
            if($this->cache != false){
                if($this->cache->isCached("uuid", $username)){
                    return $this->cache->getCache("uuid", $username);
                }else{
                    $client = new \GuzzleHttp\Client();
                    $res = $client->request('GET', $this->api."/uuid/".$username, ['http_errors' => false]);
                    $json = json_decode($res->getBody());
                    if($res->getStatusCode() == 200){
                        if($json->success){
                            $this->cache->setCache("uuid", $username, $json->uuid);
                            return $json->uuid;
                        }else{
                            return new \STAILEUAccounts\Error($res->getStatusCode(), $json->error);
                        }
                    }else{
                        return new \STAILEUAccounts\Error($res->getStatusCode(), $json->error);
                    }
                }
            }else{
                $client = new \GuzzleHttp\Client();
                $res = $client->request('GET', $this->api."/uuid/".$username, ['http_errors' => false]);
                $json = json_decode($res->getBody());
                if($res->getStatusCode() == 200){
                    if($json->success){
                        return $json->uuid;
                    }else{
                        return new \STAILEUAccounts\Error($res->getStatusCode(), $json->error);
                    }
                }else{
                    return new \STAILEUAccounts\Error($res->getStatusCode(), $json->error);
                }
            }
        }

        public function getUsername($uuid){
            if($this->cache != false){
                if($this->cache->isCached("username", $uuid)){
                    return $this->cache->getCache("username", $uuid);
                }else{
                    $client = new \GuzzleHttp\Client();
                    $res = $client->request('GET', $this->api."/username/".$uuid, ['http_errors' => false]);
                    $json = json_decode($res->getBody());
                    if($res->getStatusCode() == 200){
                        if($json->success){
                            $this->cache->setCache("username", $uuid, $json->username);
                            return $json->username;
                        }else{
                            return new \STAILEUAccounts\Error($res->getStatusCode(), $json->error);
                        }
                    }else{
                        return new \STAILEUAccounts\Error($res->getStatusCode(), $json->error);
                    }
                }
            }else{
                $client = new \GuzzleHttp\Client();
                $res = $client->request('GET', $this->api."/username/".$uuid, ['http_errors' => false]);
                $json = json_decode($res->getBody());
                if($res->getStatusCode() == 200){
                    if($json->success){
                        return $json->username;
                    }else{
                        return new \STAILEUAccounts\Error($res->getStatusCode(), $json->error);
                    }
                }else{
                    return new \STAILEUAccounts\Error($res->getStatusCode(), $json->error);
                }
            }
        }

        public function getAvatar($uuid){
            if($this->cache != false){
                if($this->cache->isCached("avatar", $uuid)){
                    return new \STAILEUAccounts\Avatar($this->cache->getCache("avatar", $uuid), $this->api."/avatar/image/".$uuid);
                }else{
                    $client = new \GuzzleHttp\Client();
                    $res = $client->request('GET', $this->api."/avatar/".$uuid, ['http_errors' => false]);
                    $json = json_decode($res->getBody());
                    if($res->getStatusCode() == 200){
                        if($json->success){
                            $this->cache->setCache("avatar", $uuid, $json->avatar);
                            return new \STAILEUAccounts\Avatar($json->avatar, $this->api."/avatar/image/".$uuid);
                        }else{
                            return new \STAILEUAccounts\Error($res->getStatusCode(), $json->error);
                        }
                    }else{
                        return new \STAILEUAccounts\Error($res->getStatusCode(), $json->error);
                    }
                }
            }else{
                $client = new \GuzzleHttp\Client();
                $res = $client->request('GET', $this->api."/avatar/".$uuid, ['http_errors' => false]);
                $json = json_decode($res->getBody());
                if($res->getStatusCode() == 200){
                    if($json->success){
                        return new \STAILEUAccounts\Avatar($json->avatar, $this->api."/avatar/image/".$uuid);
                    }else{
                        return new \STAILEUAccounts\Error($res->getStatusCode(), $json->error);
                    }
                }else{
                    return new \STAILEUAccounts\Error($res->getStatusCode(), $json->error);
                }
            }
        }

        public function getEmail($uuid){
            if($this->cache != false){
                if($this->cache->isCached("email", $uuid)){
                    return $this->cache->getCache("email", $uuid);
                }else{
                    $client = new \GuzzleHttp\Client();
                    $res = $client->request('GET', $this->api."/email/".$uuid, ['http_errors' => false]);
                    $json = json_decode($res->getBody());
                    if($res->getStatusCode() == 200){
                        if($json->success){
                            $this->cache->setCache("email", $uuid, ($json->email == null) ? "null" : $json->email);
                            return $json->email;
                        }else{
                            return new \STAILEUAccounts\Error($res->getStatusCode(), $json->error);
                        }
                    }else{
                        return new \STAILEUAccounts\Error($res->getStatusCode(), $json->error);
                    }
                }
            }else{
                $client = new \GuzzleHttp\Client();
                $res = $client->request('GET', $this->api."/email/".$uuid, ['http_errors' => false]);
                $json = json_decode($res->getBody());
                if($res->getStatusCode() == 200){
                    if($json->success){
                        return $json->email;
                    }else{
                        return new \STAILEUAccounts\Error($res->getStatusCode(), $json->error);
                    }
                }else{
                    return new \STAILEUAccounts\Error($res->getStatusCode(), $json->error);
                }
            }
        }

        public function getRegistrationDate($uuid){
            if($this->cache != false){
                if($this->cache->isCached("date", $uuid)){
                    return $this->cache->getCache("date", $uuid);
                }else{
                    $client = new \GuzzleHttp\Client();
                    $res = $client->request('GET', $this->api."/date/".$uuid, ['http_errors' => false]);
                    $json = json_decode($res->getBody());
                    if($res->getStatusCode() == 200){
                        if($json->success){
                            $this->cache->setCache("date", $uuid, $json->date);
                            return $json->date;
                        }else{
                            return new \STAILEUAccounts\Error($res->getStatusCode(), $json->error);
                        }
                    }else{
                        return new \STAILEUAccounts\Error($res->getStatusCode(), $json->error);
                    }
                }
            }else{
                $client = new \GuzzleHttp\Client();
                $res = $client->request('GET', $this->api."/date/".$uuid, ['http_errors' => false]);
                $json = json_decode($res->getBody());
                if($res->getStatusCode() == 200){
                    if($json->success){
                        return $json->date;
                    }else{
                        return new \STAILEUAccounts\Error($res->getStatusCode(), $json->error);
                    }
                }else{
                    return new \STAILEUAccounts\Error($res->getStatusCode(), $json->error);
                }
            }
        }

        public function getUsersRegistredByMe(){
            if($this->cache != false){
                if($this->cache->isCached("registered", "site")){
                    return json_decode($this->cache->getCache("registered", "site"), true);
                }else{
                    $client = new \GuzzleHttp\Client();
                    $res = $client->request('GET', $this->api."/registredbyme", ['http_errors' => false]);
                    $json = json_decode($res->getBody());
                    if($res->getStatusCode() == 200){
                        if($json->success){
                            $this->cache->setCache("registered", "site", json_encode($json->users));
                            return $json->users;
                        }else{
                            return new \STAILEUAccounts\Error($res->getStatusCode(), $json->error);
                        }
                    }else{
                        return new \STAILEUAccounts\Error($res->getStatusCode(), $json->error);
                    }
                }
            }else{
                $client = new \GuzzleHttp\Client();
                $res = $client->request('GET', $this->api."/registredbyme", ['http_errors' => false]);
                $json = json_decode($res->getBody());
                if($res->getStatusCode() == 200){
                    if($json->success){
                        return $json->users;
                    }else{
                        return new \STAILEUAccounts\Error($res->getStatusCode(), $json->error);
                    }
                }else{
                    return new \STAILEUAccounts\Error($res->getStatusCode(), $json->error);
                }
            }
        }

        public function changeUsername($username, $uuid, $CSA){
            $client = new \GuzzleHttp\Client();
            $res = $client->request('POST', $this->api."/change/username", [
                'form_params' => [
                    "c-sa" => $CSA,
                    "username" => $username,
                    "uuid" => $uuid,
                    "site_key" => $this->private_key,
                ],
                'http_errors' => false,
            ]);
            $json = json_decode($res->getBody(), true);
            if($res->getStatusCode() == 200){
                if($json['success']){
                    return true;
                }else{
                    return new \STAILEUAccounts\Error($res->getStatusCode(), $json['error']);
                }
            }else{
                return new \STAILEUAccounts\Error($res->getStatusCode(), $json['error']);
            }
        }

        public function changePassword($password, $uuid, $CSA){
            $client = new \GuzzleHttp\Client();
            $res = $client->request('POST', $this->api."/change/password", [
                'form_params' => [
                    "c-sa" => $CSA,
                    "password" => md5($password),
                    "uuid" => $uuid,
                    "site_key" => $this->private_key,
                ],
                'http_errors' => false,
            ]);
            $json = json_decode($res->getBody(), true);
            if($res->getStatusCode() == 200){
                if($json['success']){
                    return true;
                }else{
                    return new \STAILEUAccounts\Error($res->getStatusCode(), $json['error']);
                }
            }else{
                return new \STAILEUAccounts\Error($res->getStatusCode(), $json['error']);
            }
        }

        public function changeEmail($email, $uuid, $CSA){
            $client = new \GuzzleHttp\Client();
            $res = $client->request('POST', $this->api."/change/email", [
                'form_params' => [
                    "c-sa" => $CSA,
                    "email" => $email,
                    "uuid" => $uuid,
                    "site_key" => $this->private_key,
                ],
                'http_errors' => false,
            ]);
            $json = json_decode($res->getBody(), true);
            if($res->getStatusCode() == 200){
                if($json['success']){
                    return true;
                }else{
                    return new \STAILEUAccounts\Error($res->getStatusCode(), $json['error']);
                }
            }else{
                return new \STAILEUAccounts\Error($res->getStatusCode(), $json['error']);
            }
        }

        public function changeNumber($phone, $uuid, $CSA){
            $client = new \GuzzleHttp\Client();
            $res = $client->request('POST', $this->api."/change/number", [
                'form_params' => [
                    "c-sa" => $CSA,
                    "phone" => $phone,
                    "uuid" => $uuid,
                    "site_key" => $this->private_key,
                ],
                'http_errors' => false,
            ]);
            $json = json_decode($res->getBody(), true);
            if($res->getStatusCode() == 200){
                if($json['success']){
                    return true;
                }else{
                    return new \STAILEUAccounts\Error($res->getStatusCode(), $json['error']);
                }
            }else{
                return new \STAILEUAccounts\Error($res->getStatusCode(), $json['error']);
            }
        }

        public function changeAvatar($avatar, $uuid, $CSA){
            $client = new \GuzzleHttp\Client();
            $res = $client->request('POST', $this->api."/change/avatar", [
                'form_params' => [
                    "c-sa" => $CSA,
                    "avatar" => $avatar,
                    "uuid" => $uuid,
                    "site_key" => $this->private_key,
                ],
                'http_errors' => false,
            ]);
            $json = json_decode($res->getBody(), true);
            if($res->getStatusCode() == 200){
                if($json['success']){
                    return true;
                }else{
                    return new \STAILEUAccounts\Error($res->getStatusCode(), $json['error']);
                }
            }else{
                return new \STAILEUAccounts\Error($res->getStatusCode(), $json['error']);
            }
        }

        public function isEmailAddressVerified($uuid){
            $client = new \GuzzleHttp\Client();
            $res = $client->request('GET', $this->api."/verified/email/".$uuid, ['http_errors' => false]);
            $json = json_decode($res->getBody());
            if($res->getStatusCode() == 200){
                if($json->success){
                    return $json->verified;
                }else{
                    return new \STAILEUAccounts\Error($res->getStatusCode(), $json->error);
                }
            }else{
                return new \STAILEUAccounts\Error($res->getStatusCode(), $json->error);
            }
        }

        public function isPhoneNumberVerified($uuid){
            $client = new \GuzzleHttp\Client();
            $res = $client->request('GET', $this->api."/verified/phone/".$uuid, ['http_errors' => false]);
            $json = json_decode($res->getBody());
            if($res->getStatusCode() == 200){
                if($json->success){
                    return $json->verified;
                }else{
                    return new \STAILEUAccounts\Error($res->getStatusCode(), $json->error);
                }
            }else{
                return new \STAILEUAccounts\Error($res->getStatusCode(), $json->error);
            }
        }

        public function verifyPhoneNumber($uuid){
            $client = new \GuzzleHttp\Client();
            $res = $client->request('GET', $this->api."/verify/phone/".$uuid, ['http_errors' => false]);
            return ($res->getStatusCode() == 200);
        }

        public function verifyEmailAddress($uuid){
            $client = new \GuzzleHttp\Client();
            $res = $client->request('GET', $this->api."/verify/email/".$uuid, ['http_errors' => false]);
            return ($res->getStatusCode() == 200);
        }

        public function check($CSA){
            $client = new \GuzzleHttp\Client();
            $res = $client->request('POST', $this->api."/check", [
                'form_params' => [
                    "c-sa" => $CSA,
                ],
                'http_errors' => false,
            ]);
            $json = json_decode($res->getBody(), true);
            if($res->getStatusCode() == 200){
                if($json['success']){
                    return $json['uuid'];
                }else{
                    return new \STAILEUAccounts\Error($res->getStatusCode(), $json['error']);
                }
            }else{
                return new \STAILEUAccounts\Error($res->getStatusCode(), $json['error']);
            }
        }

        public function verifyCSA($CSA){
            $client = new \GuzzleHttp\Client();
            $res = $client->request('POST', $this->api."/c-sa", [
                'form_params' => [
                    "c-sa" => $CSA,
                ],
                'http_errors' => false,
            ]);
            $json = json_decode($res->getBody(), true);
            if($res->getStatusCode() == 200){
                if($json['success']){
                    return $json['active'];
                }else{
                    return new \STAILEUAccounts\Error($res->getStatusCode(), $json['error']);
                }
            }else{
                return new \STAILEUAccounts\Error($res->getStatusCode(), $json['error']);
            }
        }

    }