<?php

include_once 'vendor/autoload.php';

use Vicimus\Twicimus\OAuth;

class TestOAuth extends PHPUnit_Framework_TestCase
{
	public function testConstructor()
	{
		$key = 'xxxx';
		$secret = 'yyyy';
		$uri = 'https://api.twitter.com/oauth2/token';
		$body = 'grant_type=client_credentials';

		$oauth = new OAuth($key, $secret, $uri, $body);

		$this->assertTrue(get_class($oauth) == 'Vicimus\Twicimus\OAuth');

		return $oauth;
	}

	/**
	* @depends testConstructor
	*/
	public function testCredentials(OAuth $oauth)
	{
		$key = 'xxxx';
		$secret = 'yyyy';

		$fullToken = base64_encode(urlencode($key).':'.urlencode($secret));

		$this->assertTrue($fullToken === $oauth->getCredentials());

		return $oauth;
	}

	/**
	* @depends testCredentials
	* @expectedException        \Vicimus\Twicimus\OAuthException
    * @expectedExceptionMessage Token request failed
	*/
	public function testGetBearerTokenExcepton(OAuth $oauth)
	{
		$token = $oauth->getBearerToken();
	}

	/*
	* These last two tests require your own customer key
	* and secret. Enter your credentials and uncomment
	* the tests to include them in the tests.
	*/

	/*
	public function testGetBearerTokenSuccessReturn()
	{

		//Replace with your own key/secret in order to test
		$key = '';
		$secret = '';

		$uri = 'https://api.twitter.com/oauth2/token';
		$body = 'grant_type=client_credentials';

		$oauth = new OAuth($key, $secret, $uri, $body);

		$token = $oauth->getBearerToken();
		$this->assertInternalType('string', $token);

		return $oauth;
	}*/

	/**
	* @depends testGetBearerTokenSuccessReturn
	*/
	/*
	public function testGetBearerTokenSuccessCode(OAuth $oauth)
	{
		$this->assertEquals(200, $oauth->lastCurlInfo['http_code']);
	}*/
}