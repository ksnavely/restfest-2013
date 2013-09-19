<?php

function __autoload ( $class_name ) {
	include $class_name . '.php';
}

$barista  =  new BaristaRobot( 'http://10.0.12.137:1234' );

while ( true ) {
	$barista->look_for_work( );
	echo "Sleeping for 5 seconds...\n\n";
	sleep( 5 );
}
