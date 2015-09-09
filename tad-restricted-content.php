<?php
/**
 * Plugin Name: TAD Content Restriction
 * Plugin URI: http://theAverageDev.com
 * Description: A Post content restriction framework.
 * Version: 1.0
 * Author: theAverageDev
 * Author URI: http://theAverageDev.com
 * License: GPL 2.0
 */

include 'vendor/autoload_52.php';


if ( ! function_exists( 'trc_load' ) ) {
	function trc_load() {
		// CMB2 Init
		require_once(dirname(__FILE__) . '/vendor/webdevstudios/cmb2/init.php');

		$plugin = trc_Core_Plugin::instance();

		$plugin->file = __FILE__;
		$plugin->url  = plugins_url( '/', __FILE__ );

	}
}

// allow plugins loading on default priority to assume restriction framework is up
add_action( 'plugins_loaded', 'trc_load', 9 );
