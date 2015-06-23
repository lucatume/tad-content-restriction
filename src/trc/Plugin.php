<?php


class trc_Plugin {

	/**
	 * @var self
	 */
	protected static $instance;

	/**
	 * @var string The absolute path to the main plugin file.
	 */
	public $file;

	/**
	 * @var string The URL of the main plugin folder.
	 */
	public $url;

	/**
	 * @var string The restriction taxonomy name.
	 */
	public $restriction_taxonomy = 'trc_content_restriction';

	/**
	 * @var string The user meta key storing the content access slugs.
	 */
	public $user_content_access_slug_meta_key = '_trc_content_access_slug';

	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __get( $key ) {
		return empty( $this->$key ) ? null : $this->$key;
	}

	public function __set( $key, $value ) {
		$this->$key = $value;

		return $this;
	}
}