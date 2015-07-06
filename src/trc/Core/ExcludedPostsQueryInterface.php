<?php


interface trc_Core_ExcludedPostsQueryInterface {

	public static function instance();

	public function get_excluded_posts( $restricting_taxonomies, array $queried_restricted_post_types );

	/**
	 * @param trc_Core_FilteringTaxQueryGeneratorInterface $filtering_tax_query_generator
	 */
	public function set_filtering_tax_query_generator( $filtering_tax_query_generator );
}