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
		tad_reschedule( 'trc/core/unrestricted_posts/check' )
			->each( 120 )
			->until( array( $post_defaults, 'has_unrestricted_posts' ) )
			->with_args( $post_defaults->get_unrestricted_posts() );
	}

}