<?php


class trc_Core_PostTypes implements trc_Core_PostTypesInterface {

	/**
	 * @var string[]
	 */
	protected $restricted_post_types = array( 'post' );

	/**
	 * @return trc_Core_PostTypes
	 */
	public static function instance() {
		return new self;
	}

	/**
	 * @param $post_type
	 *
	 * @return bool
	 */
	public function is_restricted_post_type( $post_type ) {
		$post_types = is_array( $post_type ) ? $post_type : array( $post_type );
		$restricted = count( array_intersect( $post_types, $this->get_restricted_post_types() ) );

		return apply_filters( 'trc_is_restricted_post_type', (bool) $restricted, $post_type );
	}

	/**
	 * @return string[] A list of all the currently restricted post types
	 */
	public function get_restricted_post_types() {
		return apply_filters( 'trc_restricted_post_types', $this->restricted_post_types );
	}

	/**
	 * @param array|string $post_type
	 *
	 * @return $this
	 */
	public function add_restricted_post_type( $post_type ) {
		$post_types                  = is_array( $post_type ) ? $post_type : array( $post_type );
		$this->restricted_post_types = array_unique( array_merge( $this->restricted_post_types, $post_types ) );

		return $this;
	}

	/**
	 * @param array|string $post_type
	 *
	 * @return $this
	 */
	public function remove_restricted_post_type( $post_type ) {
		$post_types                  = is_array( $post_type ) ? $post_type : array( $post_type );
		$this->restricted_post_types = array_values( array_diff( $this->restricted_post_types, $post_types ) );

		return $this;
	}

	/**
	 * Prunes an array of post types to return only the restricted ones.
	 *
	 * @param string|array $post_types
	 *
	 * @return array An array containing only the restricted post types among the input ones.
	 */
	public function get_restricted_post_types_in( $post_types ) {
		$post_types = is_array( $post_types ) ? $post_types : array( $post_types );

		return array_values( array_intersect( $this->get_restricted_post_types(), $post_types ) );
	}
}