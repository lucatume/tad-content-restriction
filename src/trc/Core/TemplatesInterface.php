<?php


interface trc_Core_TemplatesInterface {

	public static function instance();

	public function get_redirection_template();

	/**
	 * @param $template
	 *
	 * @return mixed|void
	 */
	public function should_restrict_template( $template );

	public function get_unrestricted_templates();
}