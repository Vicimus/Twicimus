<?php

namespace Vicimus\Twicimus;

/**
 * OAuth Class
 * 
 * This class is used to simplify the process of using
 * OAuth to connect to an API. Designed specifically to
 * connect with the Twitter API, the class may slowly
 * evolve meet any requiremnets.
 *
 * @author Jordan Grieve <jgrieve@vicimus.com>
 * @version 1.0.0
 */

class OAuth
{
	/**
	 * Holds the encoded credentials
	 *
   	 * @var string
   	 */
	protected $credentials;

	/**
	 * Holds the bearer token returned by the OAuth endpoint.
	 *
	 * @var string  
	 */
	protected $bearerToken;

	/**
	 * Any content to be sent in the body of the OAuth request.
	 *
	 * @var string|null
	 */
	protected $oauthBody = null;

	/**
	 * The URI to to send the OAuth request
	 *
	 * @var string
	 */
	protected $oauthURI;

	/**
	 * Every time a CURL request is made, the results will
	 * be stored in this public property.
	 *
	 * @var string
	 */
	public $lastCurlInfo;

	/**
	* Instantiate an instance of OAuth
	*
	* @param string $key The customer_key to use
	* @param string $secret The customer_secret to use
	* @param string $oauthURI The base URI to connect with
	* @param string|null $oauthBody The data to send with your request
	*/
	public function __construct($key, $secret, $oauthURI, $oauthBody = null)
	{
		$this->oauthURI = $oauthURI;
		$this->oauthBody = $oauthBody;

		$this->credentials = $this->createCredentials($key, $secret);
	}

	/**
	 * Generates an array containing the headers neccessary
	 * to connect to the OAuth URI.
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
	 * View your encoded credentials that will be sent to 
	 * the OAuth URI specified.
	 * 
	 * @return string
	 */
	public function getCredentials()
	{
		return $this->credentials;
	}

	/**
	 * Set your encoded credentials using the parameters
	 * passed to the constructor of the object.
	 *
	 * @param string $key The customer key
	 * @param string $secret The customer secret
	 * 
	 * @return string
	 */
	protected function createCredentials($key, $secret)
	{
		return base64_encode(urlencode($key).':'.urlencode($secret));
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
		$ch = curl_init($this->oauthURI);

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->generateHeader());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->oauthBody);

		$results = curl_exec($ch);
		
		$this->lastCurlInfo = curl_getinfo($ch);

		if($this->lastCurlInfo['http_code'] != 200)
			throw new OAuthException('Token request failed', 
									  $this->lastCurlInfo);

		$token = json_decode($results);
		$this->bearerToken = $token->access_token;

		return $this->bearerToken;
	}
}