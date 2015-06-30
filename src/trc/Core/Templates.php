<?php


class trc_Core_Templates implements trc_Core_TemplatesInterface {

	public static function instance() {
		return new self;
	}

	public function get_redirection_template() {
		$list = array( '403.php', '404.php', 'index.php' );

		$list = apply_filters( 'trc_template_list', $list );

		return locate_template( $list );
	}

	/**
	 * @param $template
	 *
	 * @return mixed|void
	 */
	public function should_restrict_template( $template ) {

		$unrestricted_templates   = $this->get_unrestricted_templates();
		$should_restrict_template = is_singular() && ! in_array( $template, $unrestricted_templates );

		return apply_filters( 'trc_should_restrict_template', $should_restrict_template, $template );
	}

	public function get_unrestricted_templates() {
		$unrestricted = array(
			'index.php',
			'home.php',
			'archive.php',
			'comments-popup.php',
			'404.php',
			'search.php',
			'403.php',
			'front-page.php'
		);

		return apply_filters( 'trc_unrestricted_templates', $unrestricted );
	}

}