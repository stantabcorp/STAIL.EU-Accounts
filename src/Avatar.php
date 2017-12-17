<?php

namespace STAILEUAccounts;

class Avatar
{

	/**
	 * @var string $base64 The base64 avatar
	 */
	private $base64;

	/**
	 * @var string $url The avatar url
	 */
	private $url;

	public function __construct($base64, $url)
	{
		$this->base64 = $base64;
		$this->url = $url;
	}

	public function getUrl()
	{
		return $this->url;
	}

	public function getBase64()
	{
		return $this->base64;
	}

}