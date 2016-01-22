<?php

namespace Vicimus\Twicimus;

class OAuth
{
	protected $credentials;
	protected $bearerToken;
	protected $oauthPath;
	protected $oauthBody;
	protected $oauthURI;

	public function __construct($key, $secret, $oauthURI, $oauthPath = null, $oauthBody = null)
	{
		$this->oauthURI = $oauthURI;
		$this->oauthPath = is_null($oauthPath) ? '/oauth2/token' : $oauthPath;
		$this->oauthBody = is_null($oauthBody) ? 'grant_type=client_credentials' : $oauthBody;

		$encodedKey = urlencode($key);
		$encodedSecret = urlencode($secret);

		$fullToken = $encodedKey.':'.$encodedSecret;

		$encodedToken = base64_encode($fullToken);

		$this->credentials = $encodedToken;
	}

	protected function generateHeader()
	{
		$header 	= [];
		$header[] 	= 'Authorization: Basic '.$this->credentials;
		$header[] 	= 'Content-Type: application/x-www-form-urlencoded;charset=UTF-8';

		return $header;
	}

	public function getBearerToken()
	{
		$path = $this->oauthPath;
		$body = $this->oauthBody;


		$ch = curl_init($this->oauthURI.$path);

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->generateHeader());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

		$results = curl_exec($ch);
		
		$info = curl_getinfo($ch);
	
		if($info['http_code'] != 200)
			throw new \Exception('getBearerToken failed');

		$token = json_decode($results);

		$this->bearerToken = $token->access_token;

		return $this->bearerToken;
	}
}