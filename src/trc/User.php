<?php


class trc_User {

	/**
	 * @var WP_User
	 */
	protected $wp_user;

	public static function instance() {
		$instance = new self;

		$instance->set_user( get_user_by( 'id', get_current_user_id() ) );

		return $instance;
	}

	public function set_user( WP_User $user ) {
		$this->wp_user = $user;
	}

	public function get_content_access_slugs() {
		$user_meta_key = trc_Plugin::instance()->user_content_access_slug_meta_key;
		$slugs         = get_user_meta( get_current_user_id(), $user_meta_key );

		return apply_filters( 'trc_user_content_access_slugs', $slugs, get_current_user() );
	}

	public function can_access_template( $template ) {
		$can_access = false;

		if ( $this->wp_user->has_cap( 'edit_other_posts' ) ) {
			$can_access = true;
		}

		return apply_filters( 'trc_user_can_access_template', $can_access, $template, $this->wp_user );
	}

	public function can_access_query( WP_Query $query ) {
		$can_access = false;

		if ( $this->wp_user->has_cap( 'edit_other_posts' ) ) {
			$can_access = true;
		}

		return apply_filters( 'trc_user_can_access_query', $can_access, $query, $this->wp_user );

	}
}