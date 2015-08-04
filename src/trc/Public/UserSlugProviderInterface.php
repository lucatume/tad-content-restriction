<?php


interface trc_Public_UserSlugProviderInterface {

	/**
	 * @return string The name of the taxonomy the class will provide user slugs for.
	 */
	public function get_taxonomy_name();

	/**
	 * @return string[] An array of term slugs the user can access for the taxonomy.
	 */
	public function get_user_slugs();

	/**
	 * @param WP_Post $post The post object that is being updated.
	 *
	 * @return array An array of default term slugs that should be applied to each new post.
	 */
	public function get_default_post_terms(WP_Post $post);
}