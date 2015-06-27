<?php
// This is global bootstrap for autoloading

use tad\FunctionMocker\FunctionMocker;

FunctionMocker::init();

if ( ! function_exists( 'find_file_in_parent' ) ) {
	function find_file_in_parent( $path, $file = __FILE__ ) {
		$dir  = dirname( $file );
		$path = ltrim( $path, DIRECTORY_SEPARATOR );

		while ( ! file_exists( $dir . DIRECTORY_SEPARATOR . $path ) ) {
			$dir = dirname( $dir );
		}

		return $dir . DIRECTORY_SEPARATOR . $path;
	}
}
