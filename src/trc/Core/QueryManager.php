<?php


class trc_Core_QueryManager {

	/**
	 * @var WP_Query
	 */
	protected $original_query;

	/**
	 * @var WP_Query
	 */
	protected $main_query;

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
	 * @param WP_Query $query
	 *
	 * @return trc_Core_QueryManager
	 */
	public static function instance( WP_Query $query ) {
		$instance = new self();

		$instance->post_types                    = trc_Core_Plugin::instance()->post_types;
		$instance->restricting_taxonomies        = trc_Core_Plugin::instance()->taxonomies;
		$instance->filtering_tax_query_generator = trc_Core_FilteringTaxQueryGenerator::instance();
		$instance->set_query( $query );

		return $instance;
	}

	/**
	 * @param WP_Query $query
	 */
	public function set_query( WP_Query &$query ) {
		$this->main_query                  = $query;
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
	public function get_main_query() {
		return $this->main_query;
	}

	/**
	 * @param trc_Core_RestrictingTaxonomiesInterface $restricting_taxonomies
	 */
	public function set_restricting_taxonomies( trc_Core_RestrictingTaxonomiesInterface $restricting_taxonomies ) {
		$this->restricting_taxonomies = $restricting_taxonomies;
	}

	public function get_accessible_ids() {
		return $this->unaccessbile_restricted_ids;
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
	public function requires_splitting() {
		return (bool) array_diff( $this->queried_post_types, $this->queried_unrestricted_post_types );
	}

	public function analyze() {
		if ( empty( $this->main_query ) || $this->main_query->get( 'norestriction', false ) ) {
			return $this;
		}

		$queried_post_types       = $this->main_query->get( 'post_type' );
		$this->queried_post_types = is_array( $queried_post_types ) ? $queried_post_types : array( $queried_post_types );

		$this->queried_restricted_post_types   = $this->post_types->get_restricted_post_types_in( $this->queried_post_types );
		$this->queried_unrestricted_post_types = array_values( array_diff( $this->queried_post_types, $this->queried_restricted_post_types ) );

		return $this;
	}

	public function get_unaccessible_restricted_ids() {
		if ( $this->queried_unrestricted_post_types && empty( $this->unaccessbile_restricted_ids ) ) {
			/** @var \wpdb $wpdb */
			global $wpdb;

			$restricting_taxonomies = $this->restricting_taxonomies->get_restricting_taxonomies_for( $this->queried_restricted_post_types );
			foreach ( $restricting_taxonomies as $restricting_taxonomy ) {
				$tax_query     = new WP_Tax_Query( array( $this->filtering_tax_query_generator->get_tax_query_for( $restricting_taxonomy, false ) ) );
				$sql           = $tax_query->get_sql( $wpdb->posts, 'ID' );
				$post_types_in = "('" . implode( "','", $this->queried_restricted_post_types ) . "')";
				$where         = "WHERE {$wpdb->posts}.post_type IN $post_types_in {$sql['where']}";
				$query         = "SELECT ID from {$wpdb->posts} {$sql['join']} {$where}";
				$ids           = $wpdb->get_col( $query );

				$this->unaccessbile_restricted_ids = array_merge( $this->unaccessbile_restricted_ids, $ids );
			}
		}

		return $this->unaccessbile_restricted_ids;
	}
}