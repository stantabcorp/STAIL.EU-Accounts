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
			$rep = json_decode($this->sendRequest($this->urlbase."/api/uuid/get/".$user, array(), false));
			if($rep->success){
				$this->error = "";
				return $rep->uuid;
			}else{
				$this->error = $rep->error;
				throw new Exception($rep->error, 1);
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
			$rep = json_decode($this->sendRequest($this->urlbase."/api/username/get/".$user, array(), false));
			if($rep->success){
				$this->error = "";
				return $rep->username;
			}else{
				$this->error = $rep->error;
				throw new Exception($rep->error, 1);
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
			$rep = json_decode($this->sendRequest($this->urlbase."/api/avatar/get/".$user, array(), false));
			if($rep->success){
				$this->error = "";
				return $rep->avatar;
			}else{
				$this->error = $rep->error;
				throw new Exception($rep->error, 1);
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
			$rep = json_decode($this->sendRequest($this->urlbase."/api/verified/get/".$user, array(), false));
			if($rep->success){
				$this->error = "";
				return $rep->verified;
			}else{
				$this->error = $rep->error;
				throw new Exception($rep->error, 1);
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