<?php


interface trc_Core_QueryMarshalInterface {

	public static function instance();

	public function set_query( WP_Query &$query );

	public function set_query_scrutinizer( trc_Core_QueryScrutinizerInterface $query_scrutinizer );

	public function set_post_types( trc_Core_PostTypesInterface $post_types );

	public function set_restricting_taxonomies( trc_Core_RestrictingTaxonomiesInterface $restricting_taxonomies );

	public function set_filtering_tax_query_generator( trc_Core_FilteringTaxQueryGeneratorInterface $filtering_tax_query_generator );

	public function set_excluded_posts();

	public function set_filtering_tax_query();
}