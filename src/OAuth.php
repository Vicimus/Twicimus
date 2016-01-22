<?php

namespace Vicimus\Twicimus;

class OAuth
{
	protected $credentials;
	protected $bearerToken;
	protected $oauthPath;
	protected $oauthBody;
	protected $oauthURI;

	/**
	* Instantiate an instance of OAuth
	*
	* @param string $key The customer_key to use
	* @param string $secret The customer_secret to use
	* @param string $oauthURI The base URI to connect with
	* @param string|null $oauthPath The request path to connect with
	* @param string|null $oauthBody The data to send with your request
	*/
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

	/**
	* Generates an array containing the headers neccessary
	* to connect to the OAuth URI;
	*
	* @return string[]
	*/
	protected function generateHeader()
	{
		$header 	= [];
		$header[] 	= 'Authorization: Basic '.$this->credentials;
		$header[] 	= 'Content-Type: application/x-www-form-urlencoded;charset=UTF-8';

		return $header;
	}

	/**
	* Sends the request to retrieve the Bearer Token
	*
	* @throws Exception if the curl request returns a non 200
	*
	* @return string
	*/
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