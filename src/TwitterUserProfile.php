<?php

namespace Vicimus\Twicimus;

/**
 * Twitter User Profile
 *
 * This class is used to encapsulate information
 * returned by a user lookup. It holds much more detailed
 * information about a user than a standard TwitterUser instance.
 *
 * @author Jordan Grieve <jgrieve@vicimus.com>
 * @version 1.0.0
 */
class TwitterUserProfile
{
	/**
	 * URL to the users profile image
	 *
	 * @var string
	 */
	public $profile_image_url;

	/**
	 * This is an array of properties that will be collected during
	 * the parsing of the twitter data. All other properties will be ignored.
	 *
	 * @var string[]
	 */
	protected static $properties = [
		'profile_image_url'
	];

	/**
	 * Create an instance of TwitterUserProfile by passing an array of
	 * properties. This is mainly used by the TwitterRest to convert it's raw
	 * feed data into a more usable object to return.
	 *
	 * @param string[] $args An array of properties to create the profile.
	 *
	 * @return TwitterUserProfile
	 */
	public static function create(array $args)
	{
		$instance = new self;

		foreach($args as $property => $value)
			if(in_array($property, self::$properties))
				$instance->$property = $value;

		return $instance;
	}
}