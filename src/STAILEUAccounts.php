<?php 

	/**
	* STAIL.EU Accounts
	*
	* The new way to have accounts
	*
	* @author Thibault JUNIN <spamfree@thibaultjunin.fr>
	* @copyright 2015-2017 STAN-TAb Corp.
	* @license proprietary
	* @link https://stail.eu
	* @version 2.2.1
	*/
	class STAILEUAccounts{

		/**
		* @var string $key Contains the app key
		*/
		private $key;

		/**
		* @var string $pkey Contains the public key
		*/
		private $pkey;

		/**
		* @var string $urlbase Contain the API base URL
		*/
		private $url = "https://accounts.stail.eu";

		/**
		* @var string $error Should contain the last error
		*/
		private $error = "";

		/**
		* @var string $cache The cache path folder
		*/
		private $cache = "./.stail_cache";

		/**
		* @var string $caching If the cache is enable or not
		*/
		private $caching = true;

		/**
		* __construct()
		*
		* The construct method
		*
		* @param string  $key     Your app private key
		* @param string  $pkey    Your app public key
		* @param string  $cache   The cache path folder
		* @param boolean $caching If the cache is enable or not
		*/
		public function __construct($key, $pkey, $cache = "./.stail_cache", $caching = true){
			$this->key = $key;
			$this->pkey = $pkey;
			$this->cache = $cache;
			$$this->caching = $caching;
			$this->verifyKey();
			if(!file_exists($this->cache)){
				mkdir($this->cache);
			}
		}

		/**
		* setCache()
		*
		* This function set the cache
		*
		* @param string  $type 	 The type of data to save
		* @param string	 $name 	 The name of the file
		* @param string  $data 	 The data to save
		*
		* @return void
		*/
		private function setCache($type, $name, $data){
			if(!file_exists($this->cache."/".$type)){
				mkdir($this->cache."/".$type);
			}
			if(file_exists($this->cache."/".$type."/".$name)){
				$last = date("U", filemtime($this->cache."/".$type."/".$name));
				if(intval($last)+300 <= time()){
					unlink($this->cache."/".$type."/".$name);
					file_put_contents($this->cache."/".$type."/".$name, $data);
				}
			}else{
				file_put_contents($this->cache."/".$type."/".$name, $data);
			}
		}

		/**
		* getCache()
		*
		* This function get the cache
		*
		* @param string  $type 	 The type of data to save
		* @param string	 $name 	 The name of the file
		*
		* @return string The saved data
		*/
		private function getCache($type, $name){
			return file_get_contents($this->cache."/".$type."/".$name);
		}

		/**
		* isCached()
		*
		* This function check if a data is cached
		*
		* @param string  $type 	 The type of data to save
		* @param string	 $name 	 The name of the file
		*
		* @return boolean If the data is cached or not (also if the cache has expired)
		*/
		private function isCached($type, $name){
			if(!$this->caching){
				return false;
			}
			if(file_exists($this->cache."/".$type."/".$name)){
				$last = date("U", filemtime($this->cache."/".$type."/".$name));
				if(intval($last)+300 <= time()){
					return false;
				}else{
					return true;
				}
			}else{
				return false;
			}
		}

		/**
		* sendRequest()
		*
		* This function send the request to STAIL.EU's servers
		*
		* @param string  $url 	 The requested url
		* @param array 	 $params The parameters
		* @param boolean $post 	 If the request is a post request or a get
		*
		* @throws Exception If the request cannot be done
		*
		* @return string The request's response
		*/

		private function sendRequest($url, $params, $post){
			if($post){
				$options = array(
				    'http' => array(
				        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				        'method'  => 'POST',
				        'content' => http_build_query($params)
				    )
				);
				$context  = stream_context_create($options);
				$result = file_get_contents($url, false, $context);
				if ($result === FALSE) { 
					throw new Exception("Error Processing Request", 1); 
				}else{
					return $result;
				}
			}else{
				$result = file_get_contents($url);
				if($result == NULL){
					throw new Exception("Error Processing Request", 1); 
				}else{
					return $result;
				}
			}
		}

		/**
		* verifyKey()
		*
		* This function willverify the provided app key
		*
		* @throws Exception If the app key is incorect
		*/
		private function verifyKey(){
			$rep = json_decode($this->sendRequest($this->url."/api/key", array(
				"key" => $this->key,
			), true));
			if(!$rep->success){
				throw new Exception("Bad App Key !", 1);
			}
		}


		/**
		* getError()
		*
		* This function return the latest error
		*
		* @return string the latest error
		*/
		public function getError(){
			return $this->error;
		}


		/**
		* login()
		*
		* This function perform login
		*
		* @param string $user The user's username
		* @param string $pass The user's password
		*
		* @return boolean true is the user is loged in
		*/
		public function login($user, $pass){
			$rep = json_decode($this->sendRequest($this->url."/api/login", array(
				"username" => $user,
				"password" => md5($pass),
				"key" => $this->key,
			), true), true);
			if($rep['success']){
				return true;
			}else{
				$this->error = $rep['error'];
				return false;
			}
		}

		/**
		* register()
		*
		* This function register a new user
		*
		* @param array username -> The user's useranme, password -> The user's password, email -> The user's email (not required), number -> The user's mobile phone number with country code [Ex: +33695370712 OR 0033695370712] (not required), ip -> The user's ip
		*
		* @return boolean true is the user is registred
		*/
		public function register($arr){
			$data = array();
			$data['username'] = $arr['username'];
			$data['password'] = md5($arr['password']);
			if(isset($arr['email'])){
				$data['email'] = $arr['email'];
			}
			if(isset($arr['phone'])){
				$data['phone'] = $arr['number'];
			}
			$data['ip'] = $arr['ip'];
			$data['key'] = $this->key;
			$rep = json_decode($this->sendRequest($this->url."/api/register", $data, true), true);
			if($rep['success']){
				return true;
			}else{
				$this->error = $rep['error'];
				return false;
			}
		}

		/**
		* loginForm()
		*
		* This function generate the login url
		*
		* @param string $redir The redirect url
		* @param string $img OPTIONAL A image url
		*
		* @return string The login url
		*/
		public function loginForm($redir, $img = NULL){
			if($img == NULL){
				return $this->url."/login?key=".$this->pkey."&redir=".$redir;
			}else{
				return $this->url."/login?key=".$this->pkey."&redir=".$redir."&img=".$img;
			}
		}

		/**
		* registerForm()
		*
		* This function generate the register url
		*
		* @param string $redir The redirect url
		* @param string $img OPTIONAL A image url
		*
		* @return string The login url
		*/
		public function registerForm($redir, $img = NULL){
			if($img == NULL){
				return $this->url."/register?key=".$this->pkey."&redir=".$redir;
			}else{
				return $this->url."/register?key=".$this->pkey."&redir=".$redir."&img=".$img;
			}
		}

		/**
		* checkLogin()
		*
		* This function verify the return code
		*
		* @param string $code The c-sa code
		*
		* @return boolean|string false or the user's uuid
		*/
		public function checkLogin($code){
			$rep = json_decode($this->sendRequest($this->url."/api/check", array(
				"code" => $code,
				"key" => $this->key,
			), true),true);
			if($rep['success']){
				return $rep['uuid'];
			}else{
				$this->error = $rep['error'];
				return false;
			}
		}

		/**
		* forgotForm()
		*
		* This function generate the forgotten password url
		*
		* @param string $redir The redirect url
		* @param string $img OPTIONAL A image url
		*
		* @return string The login url
		*/
		public function forgotForm($redir, $img = NULL){
			if($img == NULL){
				return $this->url."/forgot?key=".$this->pkey."&redir=".$redir;
			}else{
				return $this->url."/forgot?key=".$this->pkey."&redir=".$redir."&img=".$img;
			}
			
		}

		/**
		* getEmail()
		*
		* This function get the user's email
		*
		* @param string $uuid The user's uuid
		*
		* @return string|boolean The user's email address or false
		*/
		public function getEmail($uuid){ 
			if($this->isCached("email", $uuid)){
				return $this->getCache("email", $uuid);
			}else{
				$rep = json_decode($this->sendRequest($this->url."/api/email/".$uuid, array(), false));
				if($rep->success){
					if($this->caching){
						$this->setCache("email", $uuid, $rep->email);
					}
					return $rep->email;
				}else{
					$this->error = $rep->error;
					return false;
				}
			}
			
		}

		/**
		* getUUID()
		*
		* This function return the user's uuid
		*
		* @param string $username The user's username
		*
		* @return string|boolean The user's uuid or false
		*/
		public function getUUID($username){ 
			if($this->isCached("uuid", $username)){
				return $this->getCache("uuid", $username);
			}else{
				$rep = json_decode($this->sendRequest($this->url."/api/uuid/".$username, array(), false));
				if($rep->success){
					if($this->caching){
						$this->setCache("uuid", $username, $rep->uuid);
					}
					return $rep->uuid;
				}else{
					$this->error = $rep->error;
					return false;
				}
			}
			
		}

		/**
		* getUsername()
		*
		* This function return the user's username
		*
		* @param string $uuid The user's uuid
		*
		* @return string|boolean The user's username or false
		*/
		public function getUsername($uuid){ 
			if($this->isCached("username", $uuid)){
				return $this->getCache("username", $uuid);
			}else{
				$rep = json_decode($this->sendRequest($this->url."/api/username/".$uuid, array(), false));
				if($rep->success){
					if($this->caching){
						$this->setCache("username", $uuid, $rep->username);
					}
					return $rep->username;
				}else{
					$this->error = $rep->error;
					return false;
				}
			}
		}

		/**
		* getRegisterDate()
		*
		* This function return the user's registration date
		*
		* @param string $uuid The user's uuid
		*
		* @return string|boolean The user's registration date or false
		*/
		public function getRegisterDate($uuid){ 
			if($this->isCached("date", $uuid)){
				return $this->getCache("date", $uuid);
			}else{
				$rep = json_decode($this->sendRequest($this->url."/api/date/".$uuid, array(), false));
				if($rep->success){
					if($this->caching){
						$this->setCache("date", $uuid, $rep->date);
					}
					return $rep->date;
				}else{
					$this->error = $rep->error;
					return false;
				}
			}
		}

		/**
		* getAvatar()
		*
		* This function return the user's avatar
		*
		* @param string $uuid The user's uuid
		*
		* @return string|boolean The user's avatar or false
		*/
		public function getAvatar($uuid){ 
			if($this->isCached("avatar", $uuid)){
				return $this->getCache("avatar", $uuid);
			}else{
				$rep = json_decode($this->sendRequest($this->url."/api/avatar/".$uuid, array(), false));
				if($rep->success){
					if($this->caching){
						$this->setCache("avatar", $uuid, $rep->avatar);
					}
					return $rep->avatar;
				}else{
					$this->error = $rep->error;
					return false;
				}
			}
		}

		/**
		* getAvatarURL()
		*
		* This function return the user's avatar url
		*
		* @param string $uuid The user's uuid
		*
		* @return string The user's avatar url
		*/
		public function getAvatarURL($uuid){ 
			return $this->url."/api/avatar/image/".$uuid;
		}

		/**
		* changeUsername()
		*
		* This function change the user's username
		*
		* @param string $uuid The user's uuid
		* @param string $username The new user's username
		* @param string $password The user's password
		*
		* @return boolean true If the username has been changed
		*/
		public function changeUsername($uuid, $username, $password){ 
			$rep = json_decode($this->sendRequest($this->url."/api/change/username", array(
				"uuid" => $uuid,
				"username" => $username,
				"password" => md5($password),
				"key" => $this->key,
			), true));
			if($rep->success){
				return true;
			}else{
				$this->error = $rep->error;
				return false;
			}
		}

		/**
		* changeAvatar()
		*
		* This function change the user's avatar
		*
		* @param string $uuid The user's uuid
		* @param string $avatar The user's new avatar url
		* @param string $password The user's password
		*
		* @return boolean true If the avatar has been changed
		*/
		public function changeAvatar($uuid, $avatar, $password){ 
			$rep = json_decode($this->sendRequest($this->url."/api/change/avatar", array(
				"uuid" => $uuid,
				"avatar" => $avatar,
				"password" => md5($password),
				"key" => $this->key,
			), true));
			if($rep->success){
				return true;
			}else{
				$this->error = $rep->error;
				return false;
			}
		}

		/**
		* changePassword()
		*
		* This function change the user's password
		*
		* @param string $uuid The user's uuid
		* @param string $oldpass The user's old password
		* @param string $nvpass The user's new password
		*
		* @return boolean true If the password has been changed
		*/
		public function changePassword($uuid, $oldpass, $nvpass){ 
			$rep = json_decode($this->sendRequest($this->url."/api/change/password", array(
				"uuid" => $uuid,
				"old" => md5($oldpass),
				"new" => md5($nvpass),
				"key" => $this->key,
			), true));
			if($rep->success){
				return true;
			}else{
				$this->error = $rep->error;
				return false;
			}
		}

		/**
		* changeEmail()
		*
		* This function change the user's email address
		*
		* @param string $uuid The user's uuid
		* @param string $email The new user's new email address
		* @param string $password The user's password
		*
		* @return boolean true If the email address has been changed
		*/
		public function changeEmail($uuid, $email, $password){ 
			$rep = json_decode($this->sendRequest($this->url."/api/change/email", array(
				"uuid" => $uuid,
				"email" => $email,
				"password" => md5($password),
				"key" => $this->key,
			), true));
			if($rep->success){
				return true;
			}else{
				$this->error = $rep->error;
				return false;
			}
		}

		/**
		* changeNumber()
		*
		* This function change the user's phone number
		*
		* @param string $uuid The user's uuid
		* @param string $number The user's new mobile phone number with country code
		* @param string $password The user's password
		*
		* @return boolean true If the phone number has been changed
		*/
		public function changeNumber($uuid, $number, $password){ 
			$rep = json_decode($this->sendRequest($this->url."/api/change/number", array(
				"uuid" => $uuid,
				"number" => $number,
				"password" => md5($password),
				"key" => $this->key,
			), true));
			if($rep->success){
				return true;
			}else{
				$this->error = $rep->error;
				return false;
			}
		}

		/**
		* getUserRegistredByMe()
		*
		* This function get all the users registred with the app key
		*
		* @return array List of the users registred
		*/
		public function getUserRegistredByMe(){
			if($this->isCached("userRegistredByMe", "userRegistredByMe")){
				$data = json_decode($this->getCache("userRegistredByMe", "userRegistredByMe"));
				return $data->users;
			}else{
				$rep = json_decode($this->sendRequest($this->url."/api/getUserRegistredByMe", array(
					"key" => $this->key,
				), true), true);
				if($rep['success']){
					if($this->caching){
						$this->setCache("userRegistredByMe", "userRegistredByMe", json_encode($rep));
					}
					return $rep['users'];
				}else{
					$this->error = $rep['error'];
					return false;
				}
			}
		}

		/**
		* isPhoneVerified()
		*
		* This function return is the phone number is verified or not
		*
		* @param string $uuid The user's uuid
		*
		* @return boolean If the phone number is verified or not
		*/
		public function isPhoneVerified($uuid){ 
			if($this->isCached("phone-verified", $uuid)){
				return boolval($this->getCache("phone-verified", $uuid));
			}else{
				$rep = json_decode($this->sendRequest($this->url."/api/verified/phone/".$uuid, array(), false));
				if($rep->success){
					if($this->caching){
						$this->setCache("phone-verified", $uuid, $rep->verified);
					}
					return $rep->verified;
				}else{
					$this->error = $rep->error;
					return false;
				}
			}
		}

		/**
		* isEmailVerified()
		*
		* This function return is the email address is verified or not
		*
		* @param string $uuid The user's uuid
		*
		* @return boolean If the phone number is verified or not
		*/
		public function isEmailVerified($uuid){ 
			if($this->isCached("email-verified", $uuid)){
				return boolval($this->getCache("email-verified", $uuid));
			}else{
				$rep = json_decode($this->sendRequest($this->url."/api/verified/email/".$uuid, array(), false));
				if($rep->success){
					if($this->caching){
						$this->setCache("email-verified", $uuid, $rep->verified);
					}
					return $rep->verified;
				}else{
					$this->error = $rep->error;
					return false;
				}
			}
		}

		/**
		* status()
		*
		* This function get the current STAIL.EU Accounts status
		*
		* @return array|boolean status or false if there is an error
		*/
		public function status(){
			if($this->isCached("status", "status")){
				$data = json_decode($this->getCache("status", "status"));
				return $data->users;
			}else{
				$rep = json_decode($this->sendRequest("https://stail.eu/api/status", [], false), true);
				if($rep['success']){
					if($this->caching){
						$this->setCache("status", "status", json_encode($rep));
					}
					return $rep;
				}else{
					$this->error = "Unknown error";
					return false;
				}
			}
		}

	}
