<?php

	class STAILEUAccounts{

		const FR = "fr";
		const BACK_COLOR = "e6e6e6";
		const BACKGROUND = "";
		const LOGO = "";
		const BTN_COLOR = "3fb618";
		const BORDER_COLOR = "3fb618";

		const BASE64 = 1;
		const IMAGE = 0;

		public function createUrl($array){
			if(is_array($array)){
				$fina = "https://accounts.stail.eu/";
				$parameters = array('next', 'background', 'backgroundColor', 'logo', 'btnColor', 'borderColor', 'lang');
				$i = 0;
				foreach ($array as $key => $value) {
					if(in_array($key, $parameters)){
						if($i == 0){
							$ur = urlencode($value);
							$fina .= "?{$key}={$ur}";
						}else{
							$ur = urlencode($value);
							$fina .= "&{$key}={$ur}";
						}
						$i++;
					}else{
						throw new Exception("Incorrect parameter {$key}", 1);
					}
				}
				return $fina;
			}else{
				throw new Exception("The parameter must be an array ! You can use makeArray()", 1);
			}
		}

		public function makeArray($next, $background, $backgroundColor, $logo, $btnColor, $borderColor, $lang){
			return array(
				"next" => $next,
				"background" => $background,
				"backgroundColor" => $backgroundColor,
				"logo" => $logo,
				"btnColor" => $btnColor,
				"borderColor" => $borderColor,
				"lang" => $lang,
			);
		}

		public function verifyLogin($code){
			$data = file_get_contents("https://accounts.stail.eu/API/verify_login.php?code={$code}");
			if($data != "error"){
				return $data;
			}else{
				throw new Exception("Something is wrong try again later !", 1);
			}
		}

		public function login($user, $pass){
			$pass = md5($pass);
			$data = json_decode(file_get_contents("https://accounts.stail.eu/API/website_connect.php?user={$user}&pass={$pass}"), true);
			if($data['STATUS'] == "Correct user !"){
				return true;
			}else{
				return false;
			}
		}

		public function register($user, $pass){
			$pass = md5($pass);
			$data = json_decode(file_get_contents("https://accounts.stail.eu/API/website_register.php?user={$user}&pass={$pass}"), true);
			if($data['STATUS'] == "OK User registred !"){
				return true;
			}else{
				return false;
			}
		}

		public function getAvatar($user, $base64){
			if($base64 == 1){
				return file_get_contents("https://accounts.stail.eu/API/get_avatar.php?user={$user}&base64");
			}else{
				return "https://accounts.stail.eu/API/get_avatar.php?user={$user}";
			}
		}

		public function setAvatar($user, $avatar){
			$data = file_get_contents("https://accounts.stail.eu/API/set_avatar.php?user={$user}&avatar=".urlencode($avatar));
			if($data == "Avatar changed"){
				return true;
			}else{
				return false;
			}
		}

		public function isVerified($user, $base){
			if($base == 1){
				return file_get_contents("https://accounts.stail.eu/API/is_verified.php?user=".$user."&base64");
			}else{
				return file_get_contents("https://accounts.stail.eu/API/is_verified.php?user=".$user);
			}
		}

	}