<?php 

	/**
	* STAIL.EU Accounts
	*
	* The new way to have accounts
	*
	* @author Thibault JUNIN <spamfree@thibaultjunin.fr>
	* @copyright 2015-2017 STAN-TAb Corp.
	* @license https://stantabcorp.com/license
	* @link https://stail.eu
	* @version 2.0.3
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
		* __construct()
		*
		* The construct method
		*
		* @param boolean $cache If you want to use cache or not
		*/
		public function __construct($key, $pkey){
			$this->key = $key;
			$this->pkey = $pkey;
			$this->verifyKey();
			if(!file_exists(".stail_cache")){
				mkdir(".stail_cache");
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
			if(!file_exists(".stail_cache/".$type)){
				mkdir(".stail_cache/".$type);
			}
			if(file_exists(".stail_cache/".$type."/".$name)){
				$last = date("U", filemtime(".stail_cache/".$type."/".$name));
				if(intval($last)+300 >= time()){
					unlink(".stail_cache/".$type."/".$name);
					file_put_contents(".stail_cache/".$type."/".$name, $data);
				}
			}else{
				file_put_contents(".stail_cache/".$type."/".$name, $data);
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
			return file_get_contents(".stail_cache/".$type."/".$name);
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
			if(file_exists(".stail_cache/".$type."/".$name)){
				$last = date("U", filemtime(".stail_cache/".$type."/".$name));
				if(intval($last)+600 >= time()){
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
		public function login($user, $pass){ // Fonctionne
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
		* This function perform registration
		*
		* @param array username -> The user's useranme
		*              password -> The user's password
		*              email    -> The user's email (not required)
		*              number   -> The user's mobile phone number with country code
		*              ip       -> The user's ip
		*
		* @return boolean true is the user is registred
		*/
		public function register($arr){
			$data = array();
			$data['username'] = $arr['username'];
			$data['password'] = md5($arr['password']);
			if(isset($data['email'])){
				$data['email'] = $arr['email'];
			}
			$data['phone'] = $arr['number'];
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
		* getEmail()
		*
		* This function get the user's email
		*
		* @param string The user's uuid
		*
		* @return string|boolean The user's email address or false
		*/
		public function getEmail($uuid){ 
			if($this->isCached("email", $uuid)){
				return $this->getCache("email", $uuid);
			}else{
				$rep = json_decode($this->sendRequest($this->url."/api/email/".$uuid, array(), false));
				if($rep->success){
					$this->setCache("email", $uuid, $rep->email);
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
		* @param string The user's username
		*
		* @return string|boolean The user's uuid or false
		*/
		public function getUUID($username){ 
			if($this->isCached("uuid", $username)){
				return $this->getCache("uuid", $username);
			}else{
				$rep = json_decode($this->sendRequest($this->url."/api/uuid/".$username, array(), false));
				if($rep->success){
					$this->setCache("uuid", $username, $rep->uuid);
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
		* @param string The user's uuid
		*
		* @return string|boolean The user's username or false
		*/
		public function getUsername($uuid){ 
			if($this->isCached("username", $uuid)){
				return $this->getCache("username", $uuid);
			}else{
				$rep = json_decode($this->sendRequest($this->url."/api/username/".$uuid, array(), false));
				if($rep->success){
					$this->setCache("username", $uuid, $rep->username);
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
		* @param string The user's uuid
		*
		* @return string|boolean The user's registration date or false
		*/
		public function getRegisterDate($uuid){ 
			if($this->isCached("date", $uuid)){
				return $this->getCache("date", $uuid);
			}else{
				$rep = json_decode($this->sendRequest($this->url."/api/date/".$uuid, array(), false));
				if($rep->success){
					$this->setCache("date", $uuid, $rep->date);
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
		* @param string The user's uuid
		*
		* @return string|boolean The user's avatar or false
		*/
		public function getAvatar($uuid){ 
			if($this->isCached("avatar", $uuid)){
				return $this->getCache("avatar", $uuid);
			}else{
				$rep = json_decode($this->sendRequest($this->url."/api/avatar/".$uuid, array(), false));
				if($rep->success){
					$this->setCache("avatar", $uuid, $rep->avatar);
					return $rep->avatar;
				}else{
					$this->error = $rep->error;
					return false;
				}
			}
		}

		/**
		* changeUsername()
		*
		* This function change the user's username
		*
		* @param string The user's uuid
		* @param string The new user's username
		*
		* @return boolean true If the username has been changed
		*/
		public function changeUsername($uuid, $username){ 
			$rep = json_decode($this->sendRequest($this->url."/api/change/username", array(
				"uuid" => $uuid,
				"username" => $username,
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
		* @param string The user's uuid
		* @param string The user's new avatar url
		*
		* @return boolean true If the avatar has been changed
		*/
		public function changeAvatar($uuid, $avatar){ 
			$rep = json_decode($this->sendRequest($this->url."/api/change/avatar", array(
				"uuid" => $uuid,
				"avatar" => $avatar,
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
		* @param string The user's uuid
		* @param string The user's old password
		* @param string The user's new password
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
		* @param string The user's uuid
		* @param string The new user's new email address
		*
		* @return boolean true If the email address has been changed
		*/
		public function changeEmail($uuid, $email){ 
			$rep = json_decode($this->sendRequest($this->url."/api/change/email", array(
				"uuid" => $uuid,
				"email" => $email,
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
		* This function change the user's phone number
		*
		* @param string The user's uuid
		* @param string The user's new mobile phone number with country code
		*
		* @return boolean true If the phone number has been changed
		*/
		public function changeNumber($uuid, $number){ 
			$rep = json_decode($this->sendRequest($this->url."/api/change/number", array(
				"uuid" => $uuid,
				"number" => $number,
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
					$this->setCache("userRegistredByMe", "userRegistredByMe", json_encode($rep));
					return $rep['users'];
				}else{
					$this->error = $rep['error'];
					return false;
				}
			}
		}

		/**
		* loginForm()
		*
		* This function generate the login url
		*
		* @param string The redirect url
		*
		* @return string The login url
		*/
		public function loginForm($redir){
			return "https://accounts.stail.eu/login?key=".$this->pkey."&redir=".urlencode($redir);
		}

		/**
		* registerForm()
		*
		* This function generate the register url
		*
		* @param string The redirect url
		*
		* @return string The login url
		*/
		public function registerForm($redir){
			return "https://accounts.stail.eu/register?key=".$this->pkey."&redir=".urlencode($redir);
		}

		/**
		* checkLogin()
		*
		* This function verify the return code
		*
		* @param string The c-sa code
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
		* @param string The redirect url
		*
		* @return string The login url
		*/
		public function forgotForm($redir){
			return "https://accounts.stail.eu/forgot?key=".$this->pkey."&redir=".urlencode($redir);
		}

	}
