<?php


class trc_Core_Plugin {

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
	 * @var string The user meta key storing the content access slugs.
	 */
	public $user_content_access_slug_meta_key = '_trc_content_access_slug';

	/**
	 * @var trc_Core_RestrictingTaxonomiesInterface
	 */
	public $taxonomies;

	/**
	 * @var trc_Core_UserInterface
	 */
	public $user;

	/**
	 * @var trc_Core_QueryVars
	 */
	public $query_vars;

	/**
	 * @var trc_UI_AdminPage
	 */
	public $admin_page;

	/**
	 * @var trc_Core_TemplateRedirectorInterface
	 */
	public $template_redirector;

	/**
	 * @var trc_Core_QueryRestrictorInterface
	 */
	public $query_restrictor;

	/**
	 * @var trc_Core_PostTypesInterface
	 */
	public $post_types;

	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();

			self::$instance->taxonomies          = trc_Core_RestrictingTaxonomies::instance();
			self::$instance->user                = trc_Core_User::instance();
			self::$instance->post_types          = trc_Core_PostTypes::instance();
			self::$instance->query_vars          = trc_Core_QueryVars::instance()->init();
			self::$instance->admin_page          = trc_UI_AdminPage::instance()->init();
			self::$instance->template_redirector = trc_Core_TemplateRedirector::instance()->init();
			self::$instance->query_restrictor    = trc_Core_QueryRestrictor::instance()->init();
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