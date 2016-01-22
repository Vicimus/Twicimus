<?php

namespace Vicimus\Twicimus;

class Tweet
{
	public $created_at;
	public $id;
	public $text;
	public $retweet_count;
	public $favorite_count;
	public $user;
	public $retweeted;

	protected static $properties = [
		'created_at',
		'id',
		'text',
		'retweet_count',
		'favorite_count',
		'user'
	];

	public static function create(array $args)
	{
		$instance = new self;

		foreach($args as $property => $value)
		{
			if(in_array($property, self::$properties))
			{
				if($property == 'user')
					$instance->$property = TwitterUser::create($value);
				
				elseif($property == 'created_at')
					$instance->$property = new \DateTime($value);
				
				else
					$instance->$property = $value;
			}
		}

		if(array_key_exists('retweeted_status', $args))
			$instance->retweeted = true;

		return $instance;
	}
}