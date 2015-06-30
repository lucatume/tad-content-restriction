<?php


interface trc_Core_PostTypesInterface {

	public static function instance();

	/**
	 * @return bool
	 */
	public function is_restricted_post_type( $post_type );

	public function get_restricted_post_types();
}