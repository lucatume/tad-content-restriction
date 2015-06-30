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

	/**
	 * @var trc_RestrictingTaxonomiesInterface
	 */
	public $taxonomies;

	/**
	 * @var trc_UserInterface
	 */
	public $user;

	/**
	 * @var trc_QueryVars
	 */
	public $query_vars;

	/**
	 * @var trc_AdminPage
	 */
	public $admin_page;

	/**
	 * @var trc_TemplateRedirector
	 */
	public $template_redirector;

	/**
	 * @var trc_QueryRestrictor
	 */
	public $query_restrictor;

	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();

			self::$instance->taxonomies          = trc_Taxonomies::instance();
			self::$instance->user                = trc_User::instance();
			self::$instance->query_vars          = trc_QueryVars::instance()->init();
			self::$instance->admin_page          = trc_AdminPage::instance()->init();
			self::$instance->template_redirector = trc_TemplateRedirector::instance()->init();
			self::$instance->query_restrictor    = trc_QueryRestrictor::instance()->init();
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