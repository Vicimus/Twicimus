<?php

namespace Vicimus\Twicimus;

class TwitterRest
{
	protected $oauth = null;

	const API_URI = 'https://api.twitter.com';
	const API_SEARCH = '/1.1/search/tweets.json';

	protected $output = null;

	public function __construct($key, $secret)
	{
		$this->oauth = new OAuth($key, $secret, self::API_URI);
	}

	public function bind(\Illuminate\Console\Command $output)
	{
		$this->output = $output;
	}

	protected function line($string)
	{
		if(!$this->output)
			return false;

		$this->output->line($string);
	}

	public function search($term, $count = null)
	{
		if(!is_null($count))
		{
			if(!is_numeric($count))
				throw new \Exception('Count parameter must be an integer');

			if($count < 1)
				throw new \Exception('Count parameter must be a positive integer');
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

		} while(array_key_exists('next_results', $meta) && (is_null($count) ? true : count($tweets) < $count));

        return $tweets;
	}

	protected function generateHeader()
	{
		$header 	= [];
		$header[] 	= 'Authorization: Bearer '.$this->oauth->getBearerToken();

		return $header;
	}

	protected function request($path)
	{
		$fullPath = self::API_URI.$path;
	
		$ch = curl_init($fullPath);
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->generateHeader());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$results = curl_exec($ch);
		$info = curl_getinfo($ch);

		if($info['http_code'] != 200)
			throw new \Exception('search failed with http code '.$info['http_code']);
	
		$data = json_decode($results, true);

		return $data;
	}
}