<?php

namespace Vicimus\Twicimus;

class OAuthException extends \Exception
{
	public $info;

	public function __construct($message, array $curlInfo)
	{
		parent::__construct($message);
		$this->info = $curlInfo;
	}
}