<?php 
class JsonException extends Exception {

	protected $detail;
	protected $error_code;

	public function __construct ( $error, $code ) {

		$this->error_code = $code;

		if ( $code === JSON_ERROR_DEPTH ) {
			$this->detail  =  'The maximum stack depth has been exceeded';
		}
		else if ( $code === JSON_STATE_MISMATCH ) {
			$this->detail  =  'Invalid or malformed JSON';
		}
		else if ( $code === JSON_CTRL_CHAR ) {
			$this->detail  =  'Control character error, possibly incorrectly encoded';
		}
		else if ( $code === JSON_ERROR_SYNTAX ) {
			$this->detail  =  'Syntax error';
		}
		else if ( $code === JSON_ERROR_UTF8 ) {
			$this->detail  =  'Malformed UTF-8 characters, possibly incorrectly encoded';
		}
	}
}
