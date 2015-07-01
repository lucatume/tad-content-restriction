<?php


class trc_Core_Query extends WP_Query implements trc_Core_QueryInterface {

	public static function instance( $query ) {
		$instance = new self( $query );

		return $instance;
	}
}