<?php 

	/**
	* STAIL.EU Accounts
	*
	* The new way to have accounts
	*
	* @author Thibault JUNIN <spamfree@thibaultjunin.fr>
	* @copyright 2015-2016 STAN-TAb Corp.
	* @license https://stantabcorp.com/license
	* @link https://stail.eu
	* @version 2.0.1
	*/
	class STAILEUAccounts{

		/**
		* @var string $urlbase Contain the API base URL
		*/
		private $urlbase = "https://accounts.stail.eu";
		/**
		* @var string $error Should contain the last error
		*/
		public $error = "";
		/**
		* @var boolean $cache If the cache is activated or not
		*/
		public $cache = false;

		/**
		* __construct()
		*
		* The construct method
		*
		* @param boolean $cache If you want to use cache or not
		*/
		public function __construct($cache = false){
			$this->cache = $cache;
			if($cache){
				if(!file_exists(".stail_cache")){
					mkdir(".stail_cache");
				}
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
				if(intval($last)+300 >= time()){
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
		* login()
		*
		* This function make the user log into STAIL.EU
		*
		* @param string $user The user's username
		* @param string $pass The user's password
		*
		* @return boolean true if the user is login or false if there is an error
		*/
		public function login($user, $pass){
			$rep = json_decode($this->sendRequest($this->urlbase."/api/login", array(
				"username" => $user,
				"password" => md5($pass),
			), true));
			if($rep->success){
				$this->error = "";
				return true;
			}else{
				$this->error = $rep->error;
				return false;
			}
		}

		/**
		* register()
		* 
		* This function register an user into STAIL.EU
		*
		* @param string $user The user's username
		* @param string $pass The user's password
		*
		* @return boolean true if the user is registred or false if there is an error
		*/
		public function register($user, $pass){
			$rep = json_decode($this->sendRequest($this->urlbase."/api/register", array(
				"username" => $user,
				"password" => md5($pass),
			), true));
			if($rep->success){
				$this->error = "";
				return true;
			}else{
				$this->error = $rep->error;
				return false;
			}
		}

		/**
		* getUUID()
		* 
		* This function return the user's UUID
		*
		* @param string $user The user's username
		*
		* @throws Exception If the request cannot be done
		*
		* @return string The user's UUID
		*/
		public function getUUID($user){
			if($this->cache){
				if($this->isCached("username-uuid", $user)){
					return $this->getCache("username-uuid", $user);
				}else{
					$rep = json_decode($this->sendRequest($this->urlbase."/api/uuid/get/".$user, array(), false));
					if($rep->success){
						$this->error = "";
						$this->setCache("username-uuid", $user, $rep->uuid);
						return $rep->uuid;
					}else{
						$this->error = $rep->error;
						throw new Exception($rep->error, 1);
					}
				}
			}else{
				$rep = json_decode($this->sendRequest($this->urlbase."/api/uuid/get/".$user, array(), false));
				if($rep->success){
					$this->error = "";
					return $rep->uuid;
				}else{
					$this->error = $rep->error;
					throw new Exception($rep->error, 1);
				}
			}
		}

		/**
		* getUserName()
		* 
		* This function return the user's username
		*
		* @param string $user The user's UUID
		*
		* @throws Exception If the request cannot be done
		*
		* @return string The user's username
		*/
		public function getUserName($user){
			if($this->cache){
				if($this->isCached("uuid-username", $user)){
					return $this->getCache("uuid-username", $user);
				}else{
					$rep = json_decode($this->sendRequest($this->urlbase."/api/username/get/".$user, array(), false));
					if($rep->success){
						$this->error = "";
						$this->setCache("uuid-username", $user, $rep->username);
						return $rep->username;
					}else{
						$this->error = $rep->error;
						throw new Exception($rep->error, 1);
					}
				}
			}else{
				$rep = json_decode($this->sendRequest($this->urlbase."/api/username/get/".$user, array(), false));
				if($rep->success){
					$this->error = "";
					return $rep->username;
				}else{
					$this->error = $rep->error;
					throw new Exception($rep->error, 1);
				}
			}
		}

		/**
		* getAvatar()
		* 
		* This function return the base64 encoded user's avatar
		*
		* @param string $user The user's username
		*
		* @throws Exception If the request cannot be done
		*
		* @return boolean true if the user is registred or false if there is an error
		*/
		public function getAvatar($user){
			if($this->cache){
				if($this->isCached("avatar", $user)){
					return $this->getCache("avatar", $user);
				}else{
					$rep = json_decode($this->sendRequest($this->urlbase."/api/avatar/get/".$user, array(), false));
					if($rep->success){
						$this->error = "";
						$this->setCache("avatar", $user, $rep->avatar);
						return $rep->avatar;
					}else{
						$this->error = $rep->error;
						throw new Exception($rep->error, 1);
					}
				}
			}else{
				$rep = json_decode($this->sendRequest($this->urlbase."/api/avatar/get/".$user, array(), false));
				if($rep->success){
					$this->error = "";
					return $rep->avatar;
				}else{
					$this->error = $rep->error;
					throw new Exception($rep->error, 1);
				}
			}
		}

		/**
		* setAvatar()
		* 
		* This function modify the user's avatar
		*
		* @param string $user The user's UUID
		* @param string $url  The user's avatar url (Must be readable)
		*
		* @return boolean true if the user's avatar has been changed
		*/
		public function setAvatar($user, $url){
			$rep = json_decode($this->sendRequest($this->urlbase."/api/avatar/set/".$user, array(
				"url" => $url,
			), true));
			if($rep->success){
				$this->error = "";
				return true;
			}else{
				$this->error = $rep->error;
				return false;
			}
		}

		/**
		* editUserName()
		* 
		* This function change the user's username
		*
		* @param string $user   The user's UUID
		* @param string $nvuser The user's new username
		*
		* @return boolean true if the user's username has been successfully changed
		*/
		public function editUserName($user, $nvuser){
			$rep = json_decode($this->sendRequest($this->urlbase."/api/username/edit/".$user, array(
				"username" => $nvuser,
			), true));
			if($rep->success){
				$this->error = "";
				return true;
			}else{
				$this->error = $rep->error;
				return false;
			}
		}

		/**
		* isVerified()
		* 
		* This function check is a user is verified
		*
		* @param string $user The user's UUID
		*
		* @throws Exception If the request cannot be done
		*
		* @return boolean true if the user is verified
		*/
		public function isVerified($user){
			if($this->cache){
				if($this->isCached("verified", $user)){
					return $this->getCache("verified", $user);
				}else{
					$rep = json_decode($this->sendRequest($this->urlbase."/api/verified/get/".$user, array(), false));
					if($rep->success){
						$this->error = "";
						$this->setCache("verified", $user, $rep->verified);
						return $rep->verified;
					}else{
						$this->error = $rep->error;
						throw new Exception($rep->error, 1);
					}
				}
			}else{
				$rep = json_decode($this->sendRequest($this->urlbase."/api/verified/get/".$user, array(), false));
				if($rep->success){
					$this->error = "";
					return $rep->verified;
				}else{
					$this->error = $rep->error;
					throw new Exception($rep->error, 1);
				}
			}
		}

		/**
		* askVerified()
		* 
		* This function ask STAIL.EU's staff to verify a user
		*
		* @param string $user The user's UUID
		* @param array  $data An array of user's informations Inforamtion required 
		*
		* @return boolean true if the request has been sent
		*/
		public function askVerified($user, $data){
			$rep = json_decode($this->sendRequest($this->urlbase."/api/verified/ask/".$user, array(
				"phone" => $data['phone'],
				"adresse" => $data['address'],
				"nom" => $data['name'],
				"prenom" => $data['firstname'],
				"pays" => $data['country'],
				"email" => $data['email'],
				"why" => $data['why'],
			), true));
			if($rep->success){
				$this->error = "";
				return true;
			}else{
				$this->error = $rep->error;
				return false;
			}
		}

		/**
		* editPassword()
		* 
		* This function change the user's password
		*
		* @param string $user    The user's UUID
		* @param string $oldPass The user's old password
		* @param string $newPass The user's new password
		*
		* @return boolean true if the password has been changed
		*/
		public function editPassword($user, $oldPass, $newPass){
			$rep = json_decode($this->sendRequest($this->urlbase."/api/password/edit/".$user, array(
				"actual" => md5($oldPass),
				"new" => md5($newPass),
			), true), true);
			if($rep['success']){
				$this->error = "";
				return true;
			}else{
				$this->error = $rep['error'];
				return false;
			}
		}

	}