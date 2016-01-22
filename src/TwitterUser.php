<?php

namespace Twicimus;

class TwitterUser
{
	public $id;
	public $name;
	public $screen_name;
	public $location;
	public $description;
	public $url;
	public $followers_count;
	public $friends_count;
	public $created_at;
	public $statuses_count;
	public $profile_background_color;

	protected static $properties = [
		'id',
		'name',
		'screen_name',
		'location',
		'description',
		'url',
		'followers_count',
		'friends_count',
		'created_at',
		'statuses_count',
		'profile_background_color',
	];

	public static function create(array $args)
	{
		$instance = new self;

		foreach($args as $property => $value)
			if(in_array($property, self::$properties))
				$instance->$property = $property == 'created_at' ?
					new \DateTime($value) : $value;

		return $instance;
	}
}