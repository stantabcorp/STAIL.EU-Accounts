<?php

namespace STAILEUAccounts;

class Login
{

	private $CSA;
	private $tfa;
	private $TFAToken;

	public function __construct($CSA = NULL, $tfa = false, $TFAToken = NULL)
	{
		$this->CSA = $CSA;
		$this->tfa = $tfa;
		$this->TFAToken = $TFAToken;
	}

	public function isUsingTfa()
	{
		return $this->tfa;
	}

	public function getCSAToken()
	{
		return $this->CSA;
	}

	public function getTFAToken()
	{
		return $this->TFAToken;
	}

}