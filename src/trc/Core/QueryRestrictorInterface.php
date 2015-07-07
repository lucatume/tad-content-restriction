<?php


interface trc_Core_QueryRestrictorInterface {

	public static function instance();

	public function init();

	public function maybe_restrict_query( WP_Query &$query );

	/**
	 * @param WP_Query $query
	 *
	 * @return bool
	 */
	public function should_restrict_query( WP_Query &$query );

	/**
	 * @param WP_Query $query
	 */
	public function restrict_query( WP_Query &$query );

	/**
	 * @param trc_Core_PostTypesInterface $post_types
	 */
	public function set_post_types( trc_Core_PostTypesInterface $post_types );

	/**
	 * @param trc_Core_RestrictingTaxonomiesInterface $taxonomies
	 */
	public function set_taxonomies( trc_Core_RestrictingTaxonomiesInterface $taxonomies );

	/**
	 * @param trc_Core_QueriesInterface $queries
	 */
	public function set_queries( trc_Core_QueriesInterface $queries );

	public function stop_filtering();
}