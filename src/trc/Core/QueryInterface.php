<?php


interface trc_Core_QueryInterface {

	/**
	 * @return trc_Core_IDQuery
	 */
	public static function instance();

	/**
	 * @param $key
	 * @param $value
	 *
	 * @return $this
	 */
	public function set( $key, $value );

	/**
	 * @param array $args
	 *
	 * @return $this
	 */
	public function set_args( array $args );

	/**
	 * @return array
	 */
	public function get_posts();
}