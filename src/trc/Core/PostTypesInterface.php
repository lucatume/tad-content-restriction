<?php


interface trc_Core_PostTypesInterface {

	/**
	 * @return trc_Core_PostTypesInterface
	 */
	public static function instance();

	/**
	 * @return bool
	 */
	public function is_restricted_post_type( $post_type );

	/**
	 * @return string[] A list of all the currently restricted post types.
	 */
	public function get_restricted_post_types();

	/**
	 * @param string|array $post_type
	 *
	 * @return mixed
	 */
	public function add_restricted_post_type( $post_type );

	/**
	 * @param string|array $post_type
	 *
	 * @return mixed
	 */
	public function remove_restricted_post_type( $post_type );
}