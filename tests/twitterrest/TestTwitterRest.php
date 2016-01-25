<?php

include_once 'vendor/autoload.php';

use Vicimus\Twicimus\TwitterRest;
use Vicimus\Twicimus\TwitterUser;

class TestTwitterRest extends PHPUnit_Framework_TestCase
{
	private $searchLimit = 1;

	/**
	 * Replace these values with valid key and secret to enable the test
	 *
	 * @var string $key
	 * @var string $secret
	 */
	private $key = '';
	private $secret = '';

	public function testConstructor()
	{
		$key = 'xxxx';
		$secret = 'yyyy';
		
		$api = new TwitterRest($key, $secret);

		$this->assertTrue(get_class($api) == 'Vicimus\Twicimus\TwitterRest');

		return $api;
	}

	/**
	* @depends testConstructor
	* @expectedException        \InvalidArgumentException
    * @expectedExceptionMessage Count parameter must be an integer
	*/
	public function testNonIntegerException(TwitterRest $api)
	{
		$result = $api->search('php', false);
	}

	/**
	* @depends testConstructor
	* @expectedException        \InvalidArgumentException
    * @expectedExceptionMessage Count parameter must be a positive integer
	*/
	public function testNegativeIntegerException(TwitterRest $api)
	{
		$result = $api->search('php', -1);
	}

	/**
	 * @depends testConstructor
	 * @expectedException 		\Vicimus\Twicimus\OAuthException
	 * @expectedExceptionMessage Token request failed
	 */
	public function testOAuthFailure(TwitterRest $api)
	{
		$result = $api->search('php', 10);
	}

	/* -----------------------------------------------------------------
	 * To use the below tests you must set a valid key and secret
	 * using the variables below. Without valid key and secret the tests
	 * will always fail.
	 * ---------------------------------------------------------------- */


	public function testSearch()
	{
		$key = $this->key;
      	$secret = $this->secret;

      	$api = new TwitterRest($key, $secret);

      	$results = $api->search('php', $this->searchLimit);

      	$this->assertTrue(is_array($results));

      	return $results;
	}

	/**
	 * @depends testSearch
	 */
	public function testSearchLimit(array $results)
	{
		$this->assertEquals(count($results), $this->searchLimit);
	}

	/**
	 * @depends testSearch
	 */
	public function testSearchReturnType(array $results)
	{
		$first = $results[0];

		$this->assertInstanceOf('Vicimus\Twicimus\Tweet', $first);
	}

	
	public function testEmptyResults()
	{
		$key = $this->key;
      	$secret = $this->secret;

      	$api = new TwitterRest($key, $secret);

      	$results = $api->search(md5('garbagephrase'), $this->searchLimit);

      	$this->assertTrue(empty($results));
	}

	/**
	 * @depends testSearch
	 */
	public function testTweetCreatedAt(array $results)
	{
		$tweet = $results[0];

		$now = new \DateTime();
		$diff = $now->diff($tweet->created_at);

		$this->assertInstanceOf('DateTime', $tweet->created_at);
		$this->assertTrue($diff->format('%a') <= 7);
	}

	/**
	 * @depends testSearch
	 */
	public function testTweetProperties(array $results)
	{
		$tweet = $results[0];

		$this->assertInternalType('integer', $tweet->id);
		$this->assertInternalType('string', $tweet->text);
		$this->assertInternalType('integer', $tweet->retweet_count);
		$this->assertInternalType('integer', $tweet->favorite_count);
		$this->assertInternalType('boolean', $tweet->retweeted);
	}

	/**
	 * @depends testSearch
	 */
	public function testTweetUser(array $results)
	{
		$tweet = $results[0];

		$this->assertInstanceOf('Vicimus\Twicimus\TwitterUser', $tweet->user);

		return $tweet->user;
	}

	/**
	 * @depends testTweetUser
	 */
	public function testTweetUserProperties(TwitterUser $user)
	{
		$this->assertInternalType('integer', $user->id);
		$this->assertInternalType('string', $user->name);
		$this->assertInternalType('string', $user->screen_name);
		$this->assertInternalType('string', $user->location);
		$this->assertInternalType('string', $user->description);
		$this->assertInternalType('integer', $user->followers_count);
		$this->assertInternalType('integer', $user->friends_count);
		$this->assertInternalType('integer', $user->statuses_count);
		$this->assertInternalType('string', $user->profile_background_color);

		$this->assertInstanceOf('DateTime', $user->created_at);
	}
}