<?php


class trc_Core_Scheduler {

	/**
	 * @var static
	 */
	protected static $instance;

	/**
	 * @return trc_Core_Scheduler
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function schedule() {
		// each 2' apply the default restrictions to some unrestricted posts
		$post_defaults = trc_Core_PostDefaults::instance();

		$slug_providers = trc_Core_Plugin::instance()->user->get_user_slug_providers();
		foreach ( $slug_providers as $taxonomy => $slug_provider ) {
			$post_defaults->set_user_slug_provider_for( $taxonomy, $slug_provider );
		}

		tad_reschedule( 'trc/core/unrestricted_posts/check' )
			->each( 120 )
			->until( array( $post_defaults, 'has_unrestricted_posts' ) )
			->with_args( $post_defaults->get_unrestricted_posts( array( 'limit' => 100 ) ) );
	}

}