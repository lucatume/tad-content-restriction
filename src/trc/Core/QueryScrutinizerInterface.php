<?php


interface trc_Core_QueryScrutinizerInterface {

	/**
	 * @return trc_Core_QueryScrutinizer
	 */
	public static function instance();

	/**
	 * @param WP_Query $query
	 */
	public function set_query( WP_Query &$query );

	/**
	 * @param trc_Core_PostTypesInterface $post_types
	 */
	public function set_post_types( trc_Core_PostTypesInterface $post_types );

	/**
	 * @param trc_Core_RestrictingTaxonomiesInterface $restricting_taxonomies
	 */
	public function set_restricting_taxonomies( trc_Core_RestrictingTaxonomiesInterface $restricting_taxonomies );

	public function scrutinize();

	/**
	 * @return bool
	 */
	public function is_mixed_restriction_query();


	public function is_querying_restricted_post_types();
}