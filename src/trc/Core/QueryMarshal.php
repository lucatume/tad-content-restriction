<?php


class trc_Core_QueryMarshal implements trc_Core_QueryMarshalInterface {

	/**
	 * @var WP_Query
	 */
	protected $query;

	/**
	 * @var trc_Core_QueryScrutinizerInterface
	 */
	protected $query_scrutinizer;

	/**
	 * @var trc_Core_PostTypesInterface
	 */
	protected $post_types;

	/**
	 * @var trc_Core_RestrictingTaxonomiesInterface
	 */
	protected $restricting_taxonomies;

	/**
	 * @var trc_Core_FilteringTaxQueryGeneratorInterface
	 */
	protected $filtering_tax_query_generator;

	public static function instance() {
	}

	public function set_query( WP_Query &$query ) {
		$this->query = $query;
	}

	public function set_query_scrutinizer( trc_Core_QueryScrutinizerInterface $query_scrutinizer ) {
		$this->query_scrutinizer = $query_scrutinizer;
	}

	public function set_post_types( trc_Core_PostTypesInterface $post_types ) {
		$this->post_types = $post_types;
	}

	public function set_restricting_taxonomies( trc_Core_RestrictingTaxonomiesInterface $restricting_taxonomies ) {
		$this->restricting_taxonomies = $restricting_taxonomies;
	}

	public function set_filtering_tax_query_generator( trc_Core_FilteringTaxQueryGeneratorInterface $filtering_tax_query_generator ) {
		$this->filtering_tax_query_generator = $filtering_tax_query_generator;
	}

	public function set_excluded_posts() {
	}

	public function set_filtering_tax_query() {
	}
}