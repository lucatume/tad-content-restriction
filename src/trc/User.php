<?php


class trc_User implements trc_UserInterface {

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

	public function get_content_access_slugs( $taxonomy ) {
		// @todo use user slug taxonomy provider class here
		$user_meta_key = trc_Plugin::instance()->user_content_access_slug_meta_key;
		$slugs         = get_user_meta( get_current_user_id(), $user_meta_key, true );

		$slugs = isset( $slugs[ $taxonomy ] ) ? $slugs[ $taxonomy ] : array();

		return apply_filters( 'trc_user_content_access_slugs', $slugs, get_current_user() );
	}

	public function can_access_query( WP_Query $query ) {
		$can_access = false;

		if ( $this->wp_user->has_cap( 'edit_other_posts' ) ) {
			$can_access = true;
		}

		//@todo: add access logic here

		return apply_filters( 'trc_user_can_access_query', $can_access, $query, $this->wp_user );

	}

	public function can_access_post( $post = null ) {
		$can_access = false;

		if ( $this->wp_user->has_cap( 'edit_other_posts' ) ) {
			$can_access = true;
		}

		//@todo: add access logic here

		$post = get_post();

		return apply_filters( 'trc_user_can_access_post', $can_access, $post, $this->wp_user );
	}
}