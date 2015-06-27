<?php


interface trc_UserInterface {

	/**
	 * @return trc_UserInterface
	 */
	public static function instance();

	/**
	 * @param WP_User $user
	 *
	 * @return trc_UserInterface
	 */
	public function set_user( WP_User $user );

	/**
	 * @param string $taxonomy A restriction taxonomy slug
	 *
	 * @return array An array of restriction taxonomy term slugs
	 */
	public function get_content_access_slugs( $taxonomy );

	/**
	 * @param WP_Query $query
	 *
	 * @return bool True if the user can access the query, false otherwise
	 */
	public function can_access_query( WP_Query $query );

	/**
	 * @param WP_Post|int $post Either a post object or a post ID
	 *
	 * @return bool True if the user can access the post, false otherwise
	 */
	public function can_access_post( $post = null );
}