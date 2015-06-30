<?php


class trc_Core_TemplateRedirector implements trc_Core_TemplateRedirectorInterface {

	/**
	 * @var trc_Core_PostTypesInterface
	 */
	protected $post_types;

	/**
	 * @var trc_Core_TemplatesInterface
	 */
	protected $templates;

	/**
	 * @var trc_Core_UserInterface
	 */
	protected $user;

	/**
	 * @var trc_Core_RestrictingTaxonomiesInterface
	 */
	protected $taxonomies;

	public static function instance() {
		$instance = new self;

		$instance->user       = trc_Core_Plugin::instance()->user;
		$instance->taxonomies = trc_Core_Plugin::instance()->taxonomies;
		$instance->post_types = trc_Core_PostTypes::instance();
		$instance->templates  = trc_Core_Templates::instance();

		return $instance;
	}

	/**
	 * @param trc_Core_PostTypesInterface $post_types
	 */
	public function set_post_types( trc_Core_PostTypesInterface $post_types ) {
		$this->post_types = $post_types;
	}

	public function init() {
		add_filter( 'template_include', array( $this, 'maybe_redirect' ) );

		return $this;
	}

	public function maybe_redirect( $template ) {
		if ( empty( $this->taxonomies->get_restricting_taxonomies( get_post_type() ) ) ) {
			return $template;
		}

		if ( ! $this->templates->should_restrict_template( $template ) ) {
			return $template;
		}

		if ( ! $this->post_types->is_restricted_post_type( get_post_type() ) ) {
			return $template;
		}

		if ( $this->user->can_access_post() ) {
			return $template;
		}


		return $this->templates->get_redirection_template();
	}

	/**
	 * @param trc_Core_UserInterface $user
	 */
	public function set_user( trc_Core_UserInterface $user ) {
		$this->user = $user;
	}

	/**
	 * @param trc_Core_TemplatesInterface $templates
	 */
	public function set_templates( trc_Core_TemplatesInterface $templates ) {
		$this->templates = $templates;
	}

	/**
	 * @param trc_Core_RestrictingTaxonomiesInterface $taxonomies
	 */
	public function set_taxonomies( trc_Core_RestrictingTaxonomiesInterface $taxonomies ) {
		$this->taxonomies = $taxonomies;
	}
}