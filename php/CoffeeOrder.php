<?php
class CoffeeOrder extends WorkOrder {

	protected $drink_type;
	protected $size;
	protected $addons;

	public function __construct ( array $workorder ) {

		parent::__construct( $workorder );

		$this->drink_type  =  $this->input['drink-type'];
		$this->size        =  $this->input['size-type'];
		$this->addons      =  $this->input['addons'];
	}

	public function get_size ( ) {
		return $this->size;
	}

	public function get_drink_type ( ) {
		return $this->drink_type;
	}

	public function get_addons ( ) {
		return $this->addons;
	}
}
