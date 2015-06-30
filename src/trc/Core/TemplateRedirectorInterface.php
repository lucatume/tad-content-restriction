<?php


interface trc_Core_TemplateRedirectorInterface {

	public static function instance();

	/**
	 * @param trc_Core_PostTypesInterface $post_types
	 */
	public function set_post_types( trc_Core_PostTypesInterface $post_types );

	public function init();

	public function maybe_redirect( $template );

	/**
	 * @param trc_Core_UserInterface $user
	 */
	public function set_user( trc_Core_UserInterface $user );

	/**
	 * @param trc_Core_TemplatesInterface $templates
	 */
	public function set_templates( trc_Core_TemplatesInterface $templates );

	/**
	 * @param trc_Core_RestrictingTaxonomiesInterface $taxonomies
	 */
	public function set_taxonomies( trc_Core_RestrictingTaxonomiesInterface $taxonomies );
}