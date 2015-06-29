<?php


class trc_PostTypes implements trc_PostTypesInterface {

	public static function instance() {
		return new self;
	}

	/**
	 * @return bool
	 */
	public function is_restricted_post_type( $post_type ) {
		$restricted = in_array( $post_type, $this->get_restricted_post_types() );

		return apply_filters( 'trc_is_restricted_post_type', $restricted, $post_type );
	}

	public function get_restricted_post_types() {
		$post_types = array( 'post' );

		return apply_filters( 'trc_restricted_post_types', $post_types );
	}
}