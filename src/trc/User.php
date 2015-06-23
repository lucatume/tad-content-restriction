<?php


class trc_User {

	public static function instance() {
		return new self;
	}

	public function get_content_access_slugs() {
		$user_meta_key = trc_Plugin::instance()->user_content_access_slug_meta_key;
		$slugs         = get_user_meta( get_current_user_id(), $user_meta_key );

		return apply_filters( 'trc_user_content_access_slugs', $slugs, get_current_user() );
	}
}