<?php
class Robot {

	protected $request;
	protected $base_uri;

	public function __construct ( $base_uri ) {

		$this->base_uri  =  $base_uri;
		$this->request   =  new HTTPRequest( );
	}

}
