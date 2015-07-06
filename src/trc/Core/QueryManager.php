<?php


class trc_Core_QueryScrutinizer implements trc_Core_QueryScrutinizerInterface {

	/**
	 * @var WP_Query
	 */
	protected $query;

	/**
	 * @var trc_Core_PostTypesInterface
	 */
	protected $post_types;

	/**
	 * @var trc_Core_RestrictingTaxonomiesInterface
	 */
	protected $restricting_taxonomies;

	/**
	 * @var trc_Core_QueryInterface[]
	 */
	protected $unaccessbile_restricted_ids = array();

	/**
	 * @var trc_Core_FilteringTaxQueryGeneratorInterface
	 */
	protected $filtering_tax_query_generator;

	/**
	 * @var array
	 */
	protected $queried_post_types = array();
	/**
	 * @var array
	 */
	protected $queried_unrestricted_post_types = array();

	/**
	 * @var array
	 */
	protected $queried_restricted_post_types = array();

	/**
	 * @var trc_Core_FilteringTaxQueryGeneratorInterface
	 */
	protected $filtering_taxonomy_generator;

	/**
	 * @var trc_Core_ExcludedPostsQuery
	 */
	protected $excluded_posts_query;

	/**
	 * @return trc_Core_QueryScrutinizer
	 */
	public static function instance() {
		$instance = new self();

		$instance->post_types                    = trc_Core_Plugin::instance()->post_types;
		$instance->restricting_taxonomies        = trc_Core_Plugin::instance()->taxonomies;
		$instance->filtering_tax_query_generator = trc_Core_FilteringTaxQueryGenerator::instance();
		$instance->excluded_posts_query          = trc_Core_ExcludedPostsQuery::instance();

		return $instance;
	}

	/**
	 * @param WP_Query $query
	 */
	public function set_query( WP_Query &$query ) {
		$this->query                       = $query;
		$this->unaccessbile_restricted_ids = array();

		return $this;
	}

	/**
	 * @param trc_Core_PostTypesInterface $post_types
	 */
	public function set_post_types( trc_Core_PostTypesInterface $post_types ) {
		$this->post_types = $post_types;

		return $this;
	}

	/**
	 * @return WP_Query
	 */
	public function get_query() {
		return $this->query;
	}

	/**
	 * @param trc_Core_RestrictingTaxonomiesInterface $restricting_taxonomies
	 */
	public function set_restricting_taxonomies( trc_Core_RestrictingTaxonomiesInterface $restricting_taxonomies ) {
		$this->restricting_taxonomies = $restricting_taxonomies;
	}

	/**
	 * @param trc_Core_FilteringTaxQueryGeneratorInterface $filtering_tax_query_generator
	 */
	public function set_filtering_tax_query_generator( trc_Core_FilteringTaxQueryGeneratorInterface $filtering_tax_query_generator ) {
		$this->filtering_tax_query_generator = $filtering_tax_query_generator;
	}

	/**
	 * @return bool
	 */
	public function is_mixed_restriction_query() {
		$queried_count = count( $this->queried_post_types );

		return $queried_count != count( $this->queried_unrestricted_post_types ) && $queried_count != count( $this->queried_restricted_post_types );
	}

	public function scrutinize() {
		if ( empty( $this->query ) || $this->query->get( 'norestriction', false ) ) {
			return $this;
		}

		$queried_post_types = $this->query->get( 'post_type' );

		if ( empty( $queried_post_types ) ) {
			$this->queried_post_types = array();
		} else {
			$this->queried_post_types = is_array( $queried_post_types ) ? $queried_post_types : array( $queried_post_types );
		}

		$this->queried_restricted_post_types   = $this->post_types->get_restricted_post_types_in( $this->queried_post_types );
		$this->queried_unrestricted_post_types = array_values( array_diff( $this->queried_post_types, $this->queried_restricted_post_types ) );

		return $this;
	}

	protected function get_unaccessible_restricted_ids() {
		if ( $this->queried_unrestricted_post_types && empty( $this->unaccessbile_restricted_ids ) ) {
			/** @var \wpdb $wpdb */
			global $wpdb;

			$restricting_taxonomies = $this->restricting_taxonomies->get_restricting_taxonomies_for( $this->queried_restricted_post_types );

			$this->unaccessbile_restricted_ids = $this->excluded_posts_query->get_excluded_posts( $restricting_taxonomies, $this->queried_restricted_post_types );

		}

		return $this->unaccessbile_restricted_ids;
	}

	public function set_excluded_posts() {
		$this->query->set( 'post__not_in', array_unique( array_merge( $this->query->get( 'post__not_in', array() ), $this->get_unaccessible_restricted_ids() ) ) );
	}

	public function is_querying_restricted_post_types() {
		return count( $this->queried_restricted_post_types ) > 0;
	}

	public function add_filtering_tax_query() {
		$restricting_taxonomies = $this->restricting_taxonomies->get_restricting_taxonomies_for( $this->queried_restricted_post_types );
		foreach ( $restricting_taxonomies as $restricting_tax_name ) {
			$this->query->tax_query->queries[] = $this->filtering_taxonomy_generator->get_tax_query_for( $restricting_tax_name );
		}
		$this->query->query_vars['tax_query'] = $this->query->tax_query->queries;
	}

	/**
	 * @param trc_Core_FilteringTaxQueryGeneratorInterface $filtering_taxonomy_generator
	 */
	public function set_filtering_taxonomy_generator( $filtering_taxonomy_generator ) {
		$this->filtering_taxonomy_generator = $filtering_taxonomy_generator;
	}

	public function set_excluded_posts_query( trc_Core_ExcludedPostsQueryInterface $excluded_posts_query ) {
		$this->excluded_posts_query = $excluded_posts_query;
	}
}