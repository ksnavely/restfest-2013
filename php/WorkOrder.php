<?php  

class WorkOrder {

	// For validation
	protected $required;
	protected $possible;

	// Parts of the work order
	protected $type;
	protected $start;
	protected $complete;
	protected $status;
	protected $fail;
	protected $input;


	public function __construct ( $workorder ) {
		$this->required  =  array( 'type', 'input' );
		$this->possible  =  array_merge( $this->required, 
			array( 'start', 'status', 'complete', 'fail' ) );

		$this->validate( $workorder );

		$this->type      =  $workorder['type'];
		$this->start     =  $workorder['start'];
		$this->complete  =  $workorder['complete'];
		$this->status    =  $workorder['status'];
		$this->fail      =  $workorder['fail'];
		$this->input     =  $workorder['input'];
	}


	public function validate ( $workorder ) {

		foreach ( $workorder as $key => $value ) {
			if ( ! in_array( $key, $this->possible ) ) {
				throw new Exception( "Invalid field encountered in work order: {$key}" );
			}
		}

		foreach ( $this->required as $key ) {
			if ( ! array_key_exists( $key, $workorder ) ) {
				throw new Exception( "Missing required attribute: {$key}" );
			}
		}
	}
	
	public function get_start ( ) {
		return $this->start;
	}

	public function get_complete ( ) {
		return $this->complete;
	}

}
