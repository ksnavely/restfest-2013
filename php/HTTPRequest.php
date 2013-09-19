<?php
// Basic curl library support encapsulated in HTTPRequest object.  Supports http scheme, but flexible
// for adding additional curl functionality later (ftp, ssl, header options, etc)  Calling function can
// set the User-Agent and pass an array of data to send via GET or POST methods.
class HTTPRequest {
	private $curl_obj;	// The curl session object used for this
	private $user_agent;	// Optional User-Agent string that will be sent with request
	private $data;		// Associative array of request data in key/value pairs
	private $scheme;	// Scheme specified by URL - only http currently supported
	private $host;		// Host to connect to
	private $path;		// Path information from URL
	private $port;		// Port number to connect to, if specified
	private $fragment;	// Fragment from URL

	// Description: Constructor for class is called when new class object is created
	// Parameters:
	// $url 		- URL to try and get/post
	// $request 	- associative array of request data to GET or POST to URL
	// $user_agent	- User-Agent string to send along with request for URL
	function __construct( $url = null, $data = null, $user_agent = '' ) {

		// Check if curl module is loaded
		if ( !extension_loaded( 'curl' ) ) {
			throw new Exception('cURL extension module not loaded.');
		}

		//$this->setURL( $url );

		// Initialize the curl handle
		$this->curl_obj = curl_init();

		// Set curl options to suppress output so that we can return the response
		curl_setopt( $this->curl_obj, CURLOPT_RETURNTRANSFER, true );

		// Set request data if passed, if null do nothing (calling function may have passed $user_agent)
		if ( $data != null )
			$this->setData( $data );
		else
			$this->data = array();

		// Set User-Agent string if passed to constructor
		if ( $user_agent !== '' )
			$this->setUserAgent( $user_agent );
		else
			$this->user_agent = '';

	}

	function __destruct() {
		// Free the curl handle
		curl_close( $this->curl_obj );
	}

	// Description: Send request data via GET method
	// Returns: Response text from curl
	public function get() {

		// Set url + query string using http_build_query() which will urlencode the data
		// and properly handle PHP features like multiple selections (checkboxes, select boxes)
		// that come in as a string key and array value.
		curl_setopt( $this->curl_obj, CURLOPT_URL, $this->getURL( $include_query = true ) );

		// Set the curl option for GET method - this is the default but it may have been switched
		curl_setopt( $this->curl_obj, CURLOPT_HTTPGET, true );

		// Make sure the curl option for POST is set to false in case it was used with the object before
		curl_setopt( $this->curl_obj, CURLOPT_POST, false );

		return $this->exec();
	}

	// Description: Send request data via POST method
	// Returns: Response text from curl
	public function post ( $data = null ) {

		if ( ! $data ) {
			$data  =  $this->data;
		}
		// Set URL to send POST data to
		curl_setopt( $this->curl_obj, CURLOPT_URL, $this->getURL() );

		// Set the POST options and request data
		curl_setopt( $this->curl_obj, CURLOPT_POST, true );

		// Make sure the curl option for GET is set to false in case it was used with the object before
		curl_setopt( $this->curl_obj, CURLOPT_HTTPGET, false );

		// Set up the post data
		curl_setopt( $this->curl_obj, CURLOPT_POSTFIELDS, $data );

		curl_setopt( $this->curl_obj, CURLOPT_HTTPHEADER, 
			array( "Content-Type: application/x-www-form-urlencoded" ) );

		return $this->exec();
	}

	// Description: Set the URL that we want to get
	// Parameters:
	// $url - Valid URL in the format of http://www.foo.com/bar
	public function setURL( $url ) {

		// As more curl functionality is added, other protocols like https and ftp can be added here
		// We support basic functionality without any user authentication
		static  $supported_schemes = array ( 'http' );

		// Please ask for something
		if ( trim( $url ) === '' || !is_string( $url ) ) {
			throw new Exception( 'Empty URL or non-string type specified.' );
		}

		// Chop up the URL to do some checking that we're getting an expected and supported
		// URL for the currently implemented features in the class
		$parsed_url = parse_url( $url );

		// You have to have really fouled things up to get parse_url() to return false
		if  ( $parsed_url == false ) {
			throw new Exception( 'Invalid URL specified.' );
		}

		// Check against currently implemented schemes
		if ( !in_array( $parsed_url['scheme'], $supported_schemes ) )
			throw new Exception( 'Scheme ['.$parsed_url['scheme'].'] not supported.');

		// This could be implemented later using curl but it's not supported in this class at the moment
		if ( $parsed_url['user'] || $parsed_url['pass'] ) {
			throw new Exception( 'Authentication passed on URL not supported.' );
		}

		// Don't allow query strings passed with the URL
		if ( $parsed_url['query'] ) {
			throw new Exception( 'Request data should not be sent with URL.' );
		}

		$this->scheme    =  $parsed_url['scheme'];
		$this->host      =  $parsed_url['host'];
		$this->port      =  $parsed_url['port'];
		$this->path      =  $parsed_url['path'];
		$this->fragment  =  $parsed_url['fragment'];
	}

	// Description: Construct and return the URL
	// Parameters:
	// $include_query - include the query string on the URL if true
	// Returns: URL string
	public function getURL( $include_query = false ) {
		// There is function in the pecl_http extension called http_build_url()
		// too bad it's not native.  Construct a URL with the pieces we're supporting.

		$query_string  =  '';

		// Check that the array with request data is not empty so that we do not just pass a string
		// with a question mark as the query string.
		if ( $include_query && !empty( $this->data ) ) {

			// Construct query string using http_build_query() which will urlencode the data and
			// properly handle PHP features like multiple selections (checkboxes, select boxes)
			// that come in as a string key and array value.
			$query_string = '?' . http_build_query( $this->data );
		}

		$port      =  $this->port ? (':'.$this->port):'';
		$fragment  =  $this->fragment ? ('#'.$this->fragment):'';

		return $this->scheme . '://' . $this->host . $port . $this->path . $query_string . $fragment;
	}

	// Description: Set the request data to pass along regardless of the method used.
	// Parameters:
	// $request - An array of attribute / value pairs to pass to URL
	public function setData( $data ) {

		// We're expecting an associative array of key/value pairs.  However, we're OK with
		// a one-dimensional array because it just means that the keys will be numbers.
		if ( !is_array( $data )  ) {
			throw new Exception( 'Request parameter should be an array of key / value pairs.' );
		}

		// Originally I had thought to do some checking on the keys being used for the request data
		// to make sure that no reserved characters were included, however, http_build_query() will
		// urlencode anything that needs it and according to IETF RFC 3986, percent encoded data is
		// allowed.  See https://www.ietf.org/rfc/rfc3986.txt

		// Ultimately, what I want to deal with here is that nothing funky like an array, object or
		// resource is being passed as a key.  Arrays are allowed as values, however.
		foreach ( $data as $key => $value ) {
			if ( !is_string( $key ) && !is_numeric( $key )  )
				throw new Exception( 'Invalid key specified' );

			if ( !is_string( $value ) && !is_numeric( $value )  && !is_array( $value ) )
				throw new Exception( 'Invalid value specified' );
		}

		// Everything checked out
		$this->data = $data;
	}

	// Description: Add a key / value pair to the request data after object is created.  Don't worry about
	// checking for duplicate keys because maybe we wnat to overwrite an existing value later.
	// Parameters:
	// $key - name of request variable to add
	// $value - string or array of data to be associated with variable
	public function addData( $key, $value ) {

		// Check the key
		if ( !is_string( $key ) && !is_numeric( $key )  )
			throw new Exception( 'Invalid key specified' );

		// Check the value
		if ( !is_string( $value ) && !is_numeric( $value )  && !is_array( $value ) )
			throw new Exception( 'Invalid value specified' );

		$this->data[$key] = $value;
	}

	// Description: Return the request data
	// Returns: array of request data
	public function getData() {
		return $this->data;
	}

	// Description: Set User-Agent string
	// Parameters:
	// $user_agent - String to be used
	public function setUserAgent( $user_agent ) {
		$this->user_agent = $user_agent;

		// Set User-Agent string, even if it's set to blank - the default UA string appears to be blank
		curl_setopt( $this->curl_obj, CURLOPT_USERAGENT, $this->user_agent );
	}


	// Description: Return the User-Agent string
	public function getUserAgent() {
		return $this->user_agent;
	}

	// Description: Set port number
	// Parameters:
	// $port - Port to be used
	public function setPort( $port ) {

		if ( !is_numeric( $port ) ) {
			throw new Exception( 'Non-numeric port number specified.' );
		}

		$this->port = $port;

		// Set port number in the curl options
		curl_setopt( $this->curl_obj, CURLOPT_PORT, $this->port );
	}

	// Description: Return the port number
	public function getPort() {
		return $this->port;
	}

	// Description: Call curl_exec and check for any errors
	// Returns: response if there were no errors
	private function exec( ) {
		$response = curl_exec( $this->curl_obj );

		if ( curl_errno( $this->curl_obj ) )
			throw new Exception( 'cURL Error: ' . curl_error( $this->curl_obj ) );

		return $response;
	}

	public function get_info ( ) {
		return curl_getinfo( $this->curl_obj );
	}

} // End HTTPRequest class
