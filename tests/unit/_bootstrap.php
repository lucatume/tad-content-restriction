<?php
// Here you can initialize variables that will be available to your tests
use tad\FunctionMocker\FunctionMocker;

FunctionMocker::init();

function find_file( $path, $file = __FILE__ ) {
	$dir  = dirname( $file );
	$path = ltrim( $path, DIRECTORY_SEPARATOR );
	while ( ! file_exists( $candidate = $dir . DIRECTORY_SEPARATOR . $path ) ) {
		$dir = dirname( $dir );
	}

	return $candidate;
}

include_once find_file( 'wp-includes', __FILE__ ) . '/query.php';
