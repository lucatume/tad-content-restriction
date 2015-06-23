<?php


class trc_PostTypes {

	public static function instance() {
		return new self;
	}

	public function get_restricted_post_types() {
		$post_types = array( 'post' );

		return apply_filters( 'trc_restricted_post_types', $post_types );
	}
}