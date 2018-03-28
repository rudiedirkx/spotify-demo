<?php

use rdx\http\HTTP;

class SHTTP extends HTTP {
	static public $log = [];

	public function request() {
		self::$log[] = $this->url;
		return parent::request();
	}
}
