<?php


class trc_Core_PostRestrictions extends trc_Core_AbstractUserSlugProviderClient{

	/**
	 * @var static
	 */
	protected static $instance;

	public static function instance() {
		if ( empty( self::$instance ) ) {
			self:
			$instance = new self();
		}

		return self::$instance;
	}

	public function init() {
		add_action( 'trc/core/unrestricted_posts/check', array( $this, 'apply_default_restrictions' ) );
	}

	public function apply_default_restrictions( array $unrestricted_posts = array() ) {
	}
}