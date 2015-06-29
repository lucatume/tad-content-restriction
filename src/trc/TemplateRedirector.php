<?php


class trc_TemplateRedirector {

	/**
	 * @var trc_PostTypesInterface
	 */
	protected $post_types;

	/**
	 * @var trc_TemplatesInterface
	 */
	protected $templates;

	/**
	 * @var trc_UserInterface
	 */
	protected $user;

	/**
	 * @var trc_RestrictingTaxonomiesInterface
	 */
	protected $taxonomies;

	public static function instance() {
		$instance = new self;

		$instance->post_types = trc_PostTypes::instance();
		$instance->user       = trc_User::instance();
		$instance->taxonomies = trc_Taxonomies::instance();
		$instance->templates  = trc_Templates::instance();

		return $instance;
	}

	/**
	 * @param trc_PostTypesInterface $post_types
	 */
	public function set_post_types( trc_PostTypesInterface $post_types ) {
		$this->post_types = $post_types;
	}

	public function init() {
		add_filter( 'template_include', array( $this, 'maybe_redirect' ) );

		return $this;
	}

	public function maybe_redirect( $template ) {
		if ( empty( $this->taxonomies->get_restricting_taxonomies() ) ) {
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
	 * @param trc_UserInterface $user
	 */
	public function set_user( trc_UserInterface $user ) {
		$this->user = $user;
	}

	/**
	 * @param trc_TemplatesInterface $templates
	 */
	public function set_templates( trc_TemplatesInterface $templates ) {
		$this->templates = $templates;
	}

	/**
	 * @param trc_RestrictingTaxonomiesInterface $taxonomies
	 */
	public function set_taxonomies( trc_RestrictingTaxonomiesInterface $taxonomies ) {
		$this->taxonomies = $taxonomies;
	}
}