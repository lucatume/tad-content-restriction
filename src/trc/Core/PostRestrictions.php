<?php


class trc_Core_PostRestrictions extends trc_Core_AbstractUserSlugProviderClient {

	/**
	 * @var static
	 */
	protected static $instance;

	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function init() {
		add_action( 'trc/core/unrestricted_posts/check', array( $this, 'apply_default_restrictions' ) );
	}

	public function apply_default_restrictions( array $unrestricted_posts = array() ) {
		foreach ( $unrestricted_posts as $taxonomy => $post_ids ) {
			$terms = $this->user_slug_providers[ $taxonomy ]->get_default_post_terms();
			foreach ( $post_ids as $post_id ) {
				wp_set_object_terms( $post_id, $terms, $taxonomy, true );
			}
		}
	}
}