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
	 * @return WP_Query
	 */
	public function get_query();

	/**
	 * @param trc_Core_RestrictingTaxonomiesInterface $restricting_taxonomies
	 */
	public function set_restricting_taxonomies( trc_Core_RestrictingTaxonomiesInterface $restricting_taxonomies );

	public function get_accessible_ids();

	/**
	 * @param trc_Core_FilteringTaxQueryGeneratorInterface $filtering_tax_query_generator
	 */
	public function set_filtering_tax_query_generator( trc_Core_FilteringTaxQueryGeneratorInterface $filtering_tax_query_generator );

	/**
	 * @return bool
	 */
	public function is_mixed_restriction_query();

	public function scrutinize();

	public function set_excluded_posts();

	public function is_querying_restricted_post_types();

	public function add_filtering_tax_query();

	/**
	 * @param trc_Core_FilteringTaxQueryGeneratorInterface $filtering_taxonomy_generator
	 */
	public function set_filtering_taxonomy_generator( $filtering_taxonomy_generator );
}