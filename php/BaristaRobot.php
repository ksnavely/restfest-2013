<?php

class BaristaRobot extends Robot {


	public function say ( $action, $params = null ) {

		$uri  =  $this->base_uri . $action;

		$this->request->setURL( $uri );

		if ( $params ) {
			$response  =  $this->request->post( $params );
		}
		else {
			$response  =  $this->request->get( );
		}

		//echo var_export( $this->request->get_info(), true ) . "\n" . print_r($response, true)."\n\n";

		return $response;
	}	


	public function make ( CoffeeOrder $order ) {

		$drink   =  array( $order->get_size( ), $order->get_drink_type( ) );
		$addons  =  array( );

		foreach ( $order->get_addons( ) as $addon ) {
			$addons[]  =  $addon['amount']. ' of ' . $addon['type'];
		}

		sleep( rand( 1, 10 ) );

		return implode( ' ', $drink ) . ' with ' . implode( ' and ', $addons );
	}


	protected function check_queue ( ) {

		$uri    =  $this->base_uri . '/coffee/input-queue';
		$this->request->setURL( $uri );

		$json   =  $this->request->get( );
		$queue  =  json_decode( $json, true );

		if ( ! $queue ) {
			throw new JsonException( 'json_decode failed: ', json_last_error( ) );
		}

		if ( count( $queue['collection']['items'] ) <= 0 ) {
			return array( );
		}

		// Get the details of the item
		$uri  =  $this->base_uri . $queue['collection']['items'][0]['href'];
		$this->request->setURL( $uri );
		$json       =  $this->request->get( );
		$workorder  =  json_decode( $json, true );

		if ( ! $workorder ) {
			throw new JsonException( 'json_decode failed: ', json_last_error( ) );
		}

		return $workorder;
	}


	public function look_for_work ( ) {

		// Check to see if the collection has an items
		$workorder  =  $this->check_queue( );

		if ( ! $workorder ) {
			echo "No orders waiting.\n";
			return;
		}

		try {
			$order  =  new CoffeeOrder( $workorder );
		}
		catch ( Exception $e ) {
			echo $e->getMessage( ) . "\n";
			exit( 1 );
		}

		echo "Brewing {$order->get_start( )}\n";
		$this->say( $order->get_start( ),  array( 'coffee' => 'Take this item' ) );

		$drink  =  $this->make( $order );

		echo "Completed $drink\n\n";

		$this->say( $order->get_complete( ), array( 'coffee' => $drink ) );
	}

}
