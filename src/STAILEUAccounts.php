<?php

namespace STAILEUAccounts;

use GuzzleHttp\Client;

class STAILEUAccounts
{

	private $privateKey;
	private $publicKey;
	private $cache = false;

	/**
	 * STAIL.EU api endpoint
	 *
	 * @var string
	 */
	private $endpoint = "https://api.stail.eu";

	/**
	 * STAIL.EU account endpoint
	 *
	 * @var string
	 */
	private $endpointAccount = "https://accounts.stail.eu/";

	public function __construct($privateKey, $publicKey, $cache = false, $customEndpoint = false, $customEndpointAccount = false)
	{
		if ($cache != false) {
			if (!($cache instanceof Cache)) {
				throw new \Exception("Incorrect third parameter, it MUST BE an instance of '\STAILEUAccounts\Cache' or 'false'", 1);
			}
		}
		$this->cache = $cache;
		$this->privateKey = $privateKey;
		$this->publicKey = $publicKey;
		$this->endpoint = (!$customEndpoint) ? $this->endpoint : $customEndpoint;
		$this->endpointAccount = (!$customEndpointAccount) ? $this->endpointAccount : $customEndpointAccount;
		$this->client = new Client();
	}

	public function getPrivateKey()
	{
		return $this->privateKey;
	}

	public function getApiUrl()
	{
		return $this->endpoint;
	}

	public function getPublicKey()
	{
		return $this->publicKey;
	}

	/**
	 * Do HTTP request with guzzle client
	 *
	 * @param string $verb
	 * @param string $url
	 * @param array $params
	 * @param array $otherParams
	 * @return mixed|\Psr\Http\Message\ResponseInterface
	 */
	private function doRequest(string $verb, string $url, array $params = [], array $otherParams = [])
	{
		return $this->client->request($verb, $this->endpoint . $url, [
			'form_params' => $params,
			'http_errors' => false,
			$otherParams
		]);
	}

	/**
	 * Login user with username and password
	 * Return error if fail or Login instance if success
	 *
	 * @param $username
	 * @param $password
	 * @return Error|Login
	 */
	public function login($username, $password)
	{
		$res = $this->doRequest('POST', "/login", [
			"username" => $username,
			"password" => $password,
			"site_key" => $this->privateKey,
		]);
		$json = json_decode($res->getBody(), true);
		if ($res->getStatusCode() == 200) {
			if ($json['success']) {
				return new Login(((isset($json['c-sa'])) ? $json['c-sa'] : NULL), $json['tfa'], ((isset($json['tfa_token'])) ? $json['tfa_token'] : NULL));
			} else {
				return new Error($res->getStatusCode(), $json['error']);
			}
		} else {
			return new Error($res->getStatusCode(), $json['error']);
		}
	}

	/**
	 * Register user with username, password, email (not required) and phone (not required) and user's IP
	 * Return c-sa token string if success or Error instance if fail.
	 *
	 * @param $username
	 * @param $password
	 * @param null $email
	 * @param null $phone
	 * @param $ip
	 * @return Error|string
	 */
	public function register($username, $password, $email = NULL, $phone = NULL, $ip)
	{
		$d = [
			"username" => $username,
			"password" => $password,
			"ip" => $ip,
			"site_key" => $this->privateKey,
		];
		if ($email != NULL) {
			$d['email'] = $email;
		}
		if ($phone != NULL) {
			$d['phone'] = $phone;
		}
		$res = $this->doRequest('POST', '/register', $d);
		$json = json_decode($res->getBody(), true);
		if ($res->getStatusCode() == 200) {
			if ($json['success']) {
				return (string)$json['c-sa'];
			} else {
				return new Error($res->getStatusCode(), $json['error']);
			}
		} else {
			return new Error($res->getStatusCode(), $json['error']);
		}
	}

	public function logout($CSA)
	{
		$this->doRequest('POST', "/logout", [
			'c-sa' => $CSA
		]);

		return true;
	}

	public function loginForm($redirection)
	{
		return $this->endpointAccount . "login?key=" . $this->publicKey . "&redir=" . $redirection;
	}

	public function registerForm($redirection)
	{
		return $this->endpointAccount . "register?key=" . $this->publicKey . "&redir=" . $redirection;
	}

	public function forgotForm($redirection)
	{
		return $this->endpointAccount . "forgot?key=" . $this->publicKey . "&redir=" . $redirection;
	}

	/**
	 * Get array of user's info from user's uuid
	 * WARN: this function may return 404 status because it's young route in api
	 *
	 * @param $uuid
	 * @return array|Error
	 */
	public function getUser($uuid)
	{
		$res = $this->doRequest('GET', "/user/{$uuid}");
		$json = json_decode($res->getBody());
		if ($res->getStatusCode() == 200) {
			if ($json->success) {
				return $json;
			} else {
				return new Error($res->getStatusCode(), $json->error);
			}
		} else {
			return new Error($res->getStatusCode(), $json->error);
		}
	}

	/**
	 * Get user uuid from username
	 * Return uuid string if success or Error instance if fail
	 *
	 * @param $username
	 * @return Error|string
	 */
	public function getUUID($username)
	{
		if ($this->cache != false) {
			if ($this->cache->isCached("uuid", $username)) {
				return $this->cache->getCache("uuid", $username);
			} else {
				$res = $this->doRequest('GET', "/uuid/{$username}");
				$json = json_decode($res->getBody());
				if ($res->getStatusCode() == 200) {
					if ($json->success) {
						$this->cache->setCache("uuid", $username, $json->uuid);

						return $json->uuid;
					} else {
						return new Error($res->getStatusCode(), $json->error);
					}
				} else {
					return new Error($res->getStatusCode(), $json->error);
				}
			}
		} else {
			$res = $this->doRequest('GET', "/uuid/{$username}");
			$json = json_decode($res->getBody());
			if ($res->getStatusCode() == 200) {
				if ($json->success) {
					return $json->uuid;
				} else {
					return new Error($res->getStatusCode(), $json->error);
				}
			} else {
				return new Error($res->getStatusCode(), $json->error);
			}
		}
	}

	/**
	 * Get username from uuid
	 * Return username string if success or Error instance if fail
	 *
	 * @param $uuid
	 * @return Error|string
	 */
	public function getUsername($uuid)
	{
		if ($this->cache != false) {
			if ($this->cache->isCached("username", $uuid)) {
				return $this->cache->getCache("username", $uuid);
			} else {
				$res = $this->doRequest('GET', "/username/{$uuid}");
				$json = json_decode($res->getBody());
				if ($res->getStatusCode() == 200) {
					if ($json->success) {
						$this->cache->setCache("username", $uuid, $json->username);

						return $json->username;
					} else {
						return new Error($res->getStatusCode(), $json->error);
					}
				} else {
					return new Error($res->getStatusCode(), $json->error);
				}
			}
		} else {
			$res = $this->doRequest('GET', "/username/{$uuid}");
			$json = json_decode($res->getBody());
			if ($res->getStatusCode() == 200) {
				if ($json->success) {
					return $json->username;
				} else {
					return new Error($res->getStatusCode(), $json->error);
				}
			} else {
				return new Error($res->getStatusCode(), $json->error);
			}
		}
	}

	/**
	 * Get Avatar from user's uuid
	 * Return Avatar instance if success or Error instance if fail
	 *
	 * @param $uuid
	 * @return Avatar|Error
	 */
	public function getAvatar($uuid)
	{
		if ($this->cache != false) {
			if ($this->cache->isCached("avatar", $uuid)) {
				return new Avatar($this->cache->getCache("avatar", $uuid), $this->endpoint . "/avatar/image/" . $uuid);
			} else {
				$res = $this->doRequest('GET', "/avatar/{$uuid}");
				$json = json_decode($res->getBody());
				if ($res->getStatusCode() == 200) {
					if ($json->success) {
						$this->cache->setCache("avatar", $uuid, $json->avatar);

						return new Avatar($json->avatar, $this->endpoint . "/avatar/image/" . $uuid);
					} else {
						return new Error($res->getStatusCode(), $json->error);
					}
				} else {
					return new Error($res->getStatusCode(), $json->error);
				}
			}
		} else {
			$res = $this->doRequest('GET', "/avatar/{$uuid}");
			$json = json_decode($res->getBody());
			if ($res->getStatusCode() == 200) {
				if ($json->success) {
					return new Avatar($json->avatar, $this->endpoint . "/avatar/image/" . $uuid);
				} else {
					return new Error($res->getStatusCode(), $json->error);
				}
			} else {
				return new Error($res->getStatusCode(), $json->error);
			}
		}
	}

	/**
	 * Get email from user's uuid
	 * Return email string if success or Error instance if fail
	 *
	 * @param $uuid
	 * @return Error|string
	 */
	public function getEmail($uuid)
	{
		if ($this->cache != false) {
			if ($this->cache->isCached("email", $uuid)) {
				return $this->cache->getCache("email", $uuid);
			} else {
				$res = $this->doRequest('GET', "/email/{$uuid}");
				$json = json_decode($res->getBody());
				if ($res->getStatusCode() == 200) {
					if ($json->success) {
						$this->cache->setCache("email", $uuid, ($json->email == NULL) ? "null" : $json->email);

						return $json->email;
					} else {
						return new Error($res->getStatusCode(), $json->error);
					}
				} else {
					return new Error($res->getStatusCode(), $json->error);
				}
			}
		} else {
			$res = $this->doRequest('GET', "/email/{$uuid}");
			$json = json_decode($res->getBody());
			if ($res->getStatusCode() == 200) {
				if ($json->success) {
					return $json->email;
				} else {
					return new Error($res->getStatusCode(), $json->error);
				}
			} else {
				return new Error($res->getStatusCode(), $json->error);
			}
		}
	}

	/**
	 * Get authorized email from user's uuid
	 * Return email string if success or Error instance if fail
	 *
	 * @param $uuid
	 * @return Error|string
	 */
	public function getAuthorizedEmail($uuid)
	{
		if ($this->cache != false) {
			if ($this->cache->isCached("authorized_email", $uuid)) {
				return $this->cache->getCache("authorized_email", $uuid);
			} else {
				$res = $this->doRequest('POST', "/email/{$uuid}", [
					"key" => $this->privateKey,
				]);
				$json = json_decode($res->getBody());
				if ($res->getStatusCode() == 200) {
					if ($json->success) {
						$this->cache->setCache("email", $uuid, ($json->email == NULL) ? "null" : $json->email);

						return $json->email;
					} else {
						return new Error($res->getStatusCode(), $json->error);
					}
				} else {
					return new Error($res->getStatusCode(), $json->error);
				}
			}
		} else {
			$res = $this->doRequest('POST', "/email/{$uuid}", [
				"key" => $this->privateKey,
			]);
			$json = json_decode($res->getBody());
			if ($res->getStatusCode() == 200) {
				if ($json->success) {
					return $json->email;
				} else {
					return new Error($res->getStatusCode(), $json->error);
				}
			} else {
				return new Error($res->getStatusCode(), $json->error);
			}
		}
	}

	/**
	 * Get email from user's uuid
	 * Return date time (YYYY-MM-DD HH:ii:ss) string if success or Error instance if fail
	 *
	 * @param $uuid
	 * @return Error|string
	 */
	public function getRegistrationDate($uuid)
	{
		if ($this->cache != false) {
			if ($this->cache->isCached("date", $uuid)) {
				return $this->cache->getCache("date", $uuid);
			} else {
				$res = $this->doRequest('GET', "/date/{$uuid}");
				$json = json_decode($res->getBody());
				if ($res->getStatusCode() == 200) {
					if ($json->success) {
						$this->cache->setCache("date", $uuid, $json->date);

						return $json->date;
					} else {
						return new Error($res->getStatusCode(), $json->error);
					}
				} else {
					return new Error($res->getStatusCode(), $json->error);
				}
			}
		} else {
			$res = $this->doRequest('GET', "/date/{$uuid}");
			$json = json_decode($res->getBody());
			if ($res->getStatusCode() == 200) {
				if ($json->success) {
					return $json->date;
				} else {
					return new Error($res->getStatusCode(), $json->error);
				}
			} else {
				return new Error($res->getStatusCode(), $json->error);
			}
		}
	}

	/**
	 * Get array of users registered by your site key
	 * Return array if success or Error instance if fail
	 *
	 * @return array|Error
	 */
	public function getUsersRegisteredByMe()
	{
		if ($this->cache != false) {
			if ($this->cache->isCached("registered", "site")) {
				return json_decode($this->cache->getCache("registered", "site"), true);
			} else {
				$res = $this->doRequest('GET', "/registredbyme");
				$json = json_decode($res->getBody());
				if ($res->getStatusCode() == 200) {
					if ($json->success) {
						$this->cache->setCache("registered", "site", json_encode($json->users));

						return (array)$json->users;
					} else {
						return new Error($res->getStatusCode(), $json->error);
					}
				} else {
					return new Error($res->getStatusCode(), $json->error);
				}
			}
		} else {
			$res = $this->doRequest('GET', "/registredbyme");
			$json = json_decode($res->getBody());
			if ($res->getStatusCode() == 200) {
				if ($json->success) {
					return $json->users;
				} else {
					return new Error($res->getStatusCode(), $json->error);
				}
			} else {
				return new Error($res->getStatusCode(), $json->error);
			}
		}
	}

	/**
	 * Change user's name
	 * Return true bool if success or Error instance if fail
	 *
	 * @param $username
	 * @param $uuid
	 * @param $CSA
	 * @return bool|Error
	 */
	public function changeUsername($username, $uuid, $CSA)
	{
		$res = $this->doRequest('POST', "/change/username", [
			"c-sa" => $CSA,
			"username" => $username,
			"uuid" => $uuid,
			"site_key" => $this->privateKey,
		]);
		$json = json_decode($res->getBody(), true);
		if ($res->getStatusCode() == 200) {
			if ($json['success']) {
				return true;
			} else {
				return new Error($res->getStatusCode(), $json['error']);
			}
		} else {
			return new Error($res->getStatusCode(), $json['error']);
		}
	}

	/**
	 * Change user's password
	 * Return true bool if success or Error instance if fail
	 *
	 * @param $password
	 * @param $uuid
	 * @param $CSA
	 * @return bool|Error
	 */
	public function changePassword($password, $uuid, $CSA)
	{
		$res = $this->doRequest('POST', "/change/password", [
			"c-sa" => $CSA,
			"password" => $password,
			"uuid" => $uuid,
			"site_key" => $this->privateKey,
		]);
		$json = json_decode($res->getBody(), true);
		if ($res->getStatusCode() == 200) {
			if ($json['success']) {
				return true;
			} else {
				return new Error($res->getStatusCode(), $json['error']);
			}
		} else {
			return new Error($res->getStatusCode(), $json['error']);
		}
	}

	/**
	 * Change user's email
	 * Return true bool if success or Error instance if fail
	 *
	 * @param $email
	 * @param $uuid
	 * @param $CSA
	 * @return bool|Error
	 */
	public function changeEmail($email, $uuid, $CSA)
	{
		$res = $this->doRequest('POST', "/change/email", [
			"c-sa" => $CSA,
			"email" => $email,
			"uuid" => $uuid,
			"site_key" => $this->privateKey,
		]);
		$json = json_decode($res->getBody(), true);
		if ($res->getStatusCode() == 200) {
			if ($json['success']) {
				return true;
			} else {
				return new Error($res->getStatusCode(), $json['error']);
			}
		} else {
			return new Error($res->getStatusCode(), $json['error']);
		}
	}

	public function changeNumber($phone, $uuid, $CSA)
	{
		$res = $this->doRequest('POST', "/change/number", [
			"c-sa" => $CSA,
			"phone" => $phone,
			"uuid" => $uuid,
			"site_key" => $this->privateKey,
		]);
		$json = json_decode($res->getBody(), true);
		if ($res->getStatusCode() == 200) {
			if ($json['success']) {
				return true;
			} else {
				return new Error($res->getStatusCode(), $json['error']);
			}
		} else {
			return new Error($res->getStatusCode(), $json['error']);
		}
	}

	/**
	 * Change user's avatar
	 * Return true bool if success or Error instance if fail
	 *
	 * @param $avatar
	 * @param $uuid
	 * @param $CSA
	 * @return bool|Error
	 */
	public function changeAvatar($avatar, $uuid, $CSA)
	{
		$res = $this->client->request('POST', "/change/avatar", [
			"c-sa" => $CSA,
			"avatar" => $avatar,
			"uuid" => $uuid,
			"site_key" => $this->privateKey,
		]);
		$json = json_decode($res->getBody(), true);
		if ($res->getStatusCode() == 200) {
			if ($json['success']) {
				return true;
			} else {
				return new Error($res->getStatusCode(), $json['error']);
			}
		} else {
			return new Error($res->getStatusCode(), $json['error']);
		}
	}

	public function isEmailAddressVerified($uuid)
	{
		$res = $this->doRequest('GET', "/verified/email/{$uuid}");
		$json = json_decode($res->getBody());
		if ($res->getStatusCode() == 200) {
			if ($json->success) {
				return $json->verified;
			} else {
				return new Error($res->getStatusCode(), $json->error);
			}
		} else {
			return new Error($res->getStatusCode(), $json->error);
		}
	}

	public function isPhoneNumberVerified($uuid)
	{
		$res = $this->doRequest('GET', "/verified/phone/{$uuid}");
		$json = json_decode($res->getBody());
		if ($res->getStatusCode() == 200) {
			if ($json->success) {
				return $json->verified;
			} else {
				return new Error($res->getStatusCode(), $json->error);
			}
		} else {
			return new Error($res->getStatusCode(), $json->error);
		}
	}

	public function verifyPhoneNumber($uuid)
	{
		$res = $this->doRequest('GET', "/verify/phone/{$uuid}");

		return ($res->getStatusCode() == 200);
	}

	public function verifyEmailAddress($uuid)
	{
		$res = $this->doRequest('GET', "/verify/email/{$uuid}");

		return ($res->getStatusCode() == 200);
	}

	/**
	 * Check if csa token is verified and get user's uuid binding
	 *
	 * @param $CSA
	 * @return Error|string
	 */
	public function check($CSA)
	{
		$res = $this->doRequest('POST', "/check", [
			"c-sa" => $CSA
		]);
		$json = json_decode($res->getBody(), true);
		if ($res->getStatusCode() == 200) {
			if ($json['success']) {
				return (string)$json['uuid'];
			} else {
				return new Error($res->getStatusCode(), $json['error']);
			}
		} else {
			return new Error($res->getStatusCode(), $json['error']);
		}
	}

	public function verifyCSA($CSA)
	{
		$res = $this->doRequest('POST', "/c-sa", [
			"c-sa" => $CSA,
		]);
		$json = json_decode($res->getBody(), true);
		if ($res->getStatusCode() == 200) {
			if ($json['success']) {
				return $json['active'];
			} else {
				return new Error($res->getStatusCode(), $json['error']);
			}
		} else {
			return new Error($res->getStatusCode(), $json['error']);
		}
	}

}
