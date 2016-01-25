<?php

namespace Vicimus\Twicimus;

use \Illuminate\Console\Command;

/**
 * Twitter Rest API
 * 
 * This class is used to interact with the Twitter
 * REST API using OAuth2.
 *
 * @author Jordan Grieve <jgrieve@vicimus.com>
 * @version 1.0.0
 */
class TwitterRest
{
	/**
	 * Twitter API address
	 * 
	 * @var string
	 */
	const API_URI = 'https://api.twitter.com';

	/**
	 * The path to the Twitter Oauth Token request
	 * 
	 * @var string
	 */
	const API_OAUTH_PATH = '/oauth2/token';

	/**
	 * The required body content when sending an OAuth request to
	 * to the Twitter API
	 * 
	 * @var string
	 */
	const API_OAUTH_BODY = 'grant_type=client_credentials';

	/**
	 * The URI to execute a search using the Twitter API
	 * 
	 * @var string
	 */
	const API_SEARCH = '/1.1/search/tweets.json';

	/**
	 * The URI to execute a user lookup using the Twitter API
	 *
	 * @var string
	 */
	const API_USER_LOOKUP = '/1.1/users/show.json';

	/**
	 * This holds the OAuth instance used to authenticate with the Twitter
	 * API.
	 * 
	 * @var \Vicimus\Twicimus\OAuth $oauth
	 */
	protected $oauth = null;

	/**
	* Can hold an instance of Command used to output progress. This is
	* used primarily with a Laravel artisan command.
	*
	* @var \Illuminate\Console\Command
	*/
	protected $output = null;

	/**
	 * Instantiate an instance of the Twitter REST API
	 *
	 * @param string $key The customer_key to use
	 * @param string $secret The customer_secret to use
	 */
	public function __construct($key, $secret)
	{
		$this->oauth = new OAuth(
			$key, 
			$secret, 
			self::API_URI.self::API_OAUTH_PATH, 
			self::API_OAUTH_BODY
		);
	}

	/**
	 * Binds an instance of Command to be used throughout
	 * the class as a means to output state and progress.
	 * This will typically be used if this class is being
	 * utilized in a Laravel artisan command.
	 *
	 * @param Command $output Sets an instance of Command to be used.
	 *
	 * @return void
	 */
	public function bind(Command $output)
	{
		$this->output = $output;
	}

	/**
	 * Executes a Twitter Search using the Twitter REST API. If results are
	 * found, it will return an array of Tweet objects that will contain
	 * various amounts of data related to the Tweets matching your search
	 * term.
	 *
	 * @param string $term The term to search for
	 * @param integer|null $count optional the maximum number of results
	 *
	 * @throws InvalidArgumentException if $count is not an integer
	 * @throws InvalidArgumentException if $count is not positive
	 *
	 * @return Tweet[] Returns an array of Tweet objects
	 */
	public function search($term, $count = null)
	{
		if(!is_null($count))
		{
			if(!is_numeric($count))
				throw new \InvalidArgumentException(
					'Count parameter must be an integer');

			if($count < 1)
				throw new \InvalidArgumentException(
					'Count parameter must be a positive integer');
		}
		
		$path = self::API_SEARCH.'?q='.urlencode($term);
		if(!is_null($count))
			$path .= '&count='.$count;
		else
			$path .= '&count=100';

		$tweets = [];

		$this->line('Starting fetch');

		do {
			
			$data = $this->request($path);
			$meta = $data['search_metadata'];
			
        	foreach($data['statuses'] as $property => $status)
            	$tweets[] = Tweet::create($status);

            if(array_key_exists('next_results', $meta))
            	$path = self::API_SEARCH.$meta['next_results'];

            $this->line("\r".count($tweets).' Gathered');

		} while
		(
			array_key_exists('next_results', $meta) && 
			(is_null($count) ? true : count($tweets) < $count)
		);

        return $tweets;
	}

	/**
	 * Executes a Twitter User Lookup using the Twitter REST API. If
	 * a user is found, an object containg the resulst will be returned.
	 *
	 * @param ulong $userID 	The Twitter User ID to lookup
	 *
	 * @throws InvalidArgumentException if $userID is not an integer
	 * @throws InvalidArgumentException if $userID is negative
	 *
	 * @return \stdClass
	 */
	public function lookup($userID)
	{
		if(!is_numeric($userID))
			throw new \InvalidArgumentException(
				'Count parameter must be an integer');

		if($userID < 1)
			throw new \InvalidArgumentException(
				'Count parameter must be a positive integer');

		$path = self::API_USER_LOOKUP.'?user_id='.$userID;

		$this->line("\r"."Looking Up: ".$userID);

		$request = $this->request($path);

		return TwitterUserProfile::create($request);
	}

	/**
	 * Uses the bound Command object to output text to the screen.
	 * If there is no Command object bound, it simply does nothing.
	 *
	 * @param string The string to output
	 *
	 * @return void
	 */
	protected function line($string)
	{
		if(!$this->output)
			return false;

		$this->output->line($string);
	}

	/**
	 * This generates the headers neccessary to communicate with
	 * the Twitter REST API.
	 *
	 * @return string[] Returns the header array used to authenticate
	 */
	protected function generateHeader()
	{
		$header 	= [];
		$header[] 	= 'Authorization: Bearer '.$this->oauth->getBearerToken();

		return $header;
	}

	/**
	 * Executes a CURL request to the given path.
	 *
	 * @param string $path The URI to append to the API_URI
	 *
	 * @throws Exception If the curl request returns non-200 response
	 *
	 * @return string[] Returns an array of data returned by the API
	 */
	protected function request($path)
	{
		$fullPath = self::API_URI.$path;
	
		$ch = curl_init($fullPath);
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->generateHeader());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$results = curl_exec($ch);
		$info = curl_getinfo($ch);

		if($info['http_code'] != 200)
			throw new \Exception('failed with http code '.$info['http_code']);
	
		$data = json_decode($results, true);

		return $data;
	}
}