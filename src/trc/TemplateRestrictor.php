<?php


class trc_TemplateRestrictor {

	/**
	 * @var trc_PostTypes
	 */
	protected $post_types;

	/**
	 * @var trc_Templates
	 */
	protected $templates;

	/**
	 * @var trc_User
	 */
	protected $user;

	public static function instance() {
		$instance = new self;

		$instance->set_post_types( trc_PostTypes::instance() );

		return $instance;
	}

	public function set_post_types( trc_PostTypes $post_types ) {
		$this->post_types = $post_types;
	}

	public function init() {
		add_filter( 'template_include', array( $this, 'maybe_redirect' ) );

		return $this;
	}

	public function maybe_redirect( $template ) {
		if ( ! $this->templates->should_restrict_template( $template ) ) {
			return $template;
		}

		if ( ! $this->post_types->is_restricted_post_type( get_post_type() ) ) {
			return $template;
		}

		if ( $this->user->can_access_template( $template ) ) {
			return $template;
		}


		return $this->templates->get_redirection_template();
	}

	public function set_user( trc_User $user ) {
		$this->user = $user;
	}

	/**
	 * @param trc_Templates $templates
	 */
	public function set_templates( $templates ) {
		$this->templates = $templates;
	}
}