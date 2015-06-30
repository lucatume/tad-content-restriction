<?php


interface trc_UserInterface {

	public static function instance();

	/**
	 * @param WP_User $user
	 */
	public function set_user( WP_User $user );

	/**
	 * @param int|WP_Post|null $post A post ID, a post object or null to use the current globally defined post.
	 *
	 * @return bool|WP_Error True if the user can access the post, false if the user cannot access the post, a WP_Error
	 *                       if the post parameter is not valid.
	 */
	public function can_access_post( $post = null );

	/**
	 * @param string $tax
	 *
	 * @return array|string[]
	 */
	public function get_user_slugs_for( $tax );

	/**
	 * @return trc_UserSlugProviderInterface[]
	 */
	public function get_user_slug_providers();

	/**
	 * @param string                        $taxonomy
	 * @param trc_UserSlugProviderInterface $user_slug_provider
	 *
	 * @return $this
	 */
	public function add_user_slug_provider( $taxonomy, trc_UserSlugProviderInterface $user_slug_provider );

	/**
	 * @param $taxonomy
	 *
	 * @return $this
	 */
	public function remove_user_slug_provider( $taxonomy );

	/**
	 * @param trc_RestrictingTaxonomiesInterface $taxonomies
	 */
	public function set_taxonomies( trc_RestrictingTaxonomiesInterface $taxonomies );
}