<?php


class trc_Core_QueryRestrictor implements trc_Core_QueryRestrictorInterface {

	/**
	 * @var trc_Core_PostTypesInterface
	 */
	protected $post_types;

	/**
	 * @var trc_Core_RestrictingTaxonomiesInterface
	 */
	protected $taxonomies;

	/**
	 * @var trc_Core_FilteringTaxQueryGeneratorInterface
	 */
	protected $filtering_taxonomy_generator;

	/**
	 * @var trc_Core_QueriesInterface
	 */
	protected $queries;

	public static function instance() {
		$instance = new self;

		$instance->taxonomies                   = trc_Core_Plugin::instance()->taxonomies;
		$instance->post_types                   = trc_Core_Plugin::instance()->post_types;
		$instance->filtering_taxonomy_generator = trc_Core_FilteringTaxQueryGenerator::instance();
		$instance->queries                      = trc_Core_Queries::instance();

		return $instance;
	}

	public function init() {
		if ( is_admin() ) {
			// restrictions will not apply to back-end
			return $this;
		}
		add_action( 'pre_get_posts', array( $this, 'maybe_restrict_query' ), 10, 1 );

		return $this;
	}

	public function maybe_restrict_query( WP_Query &$query ) {
		if ( ! $this->should_restrict_query( $query ) ) {
			return;
		}

		$this->restrict_query( $query );
	}

	/**
	 * @param WP_Query $query
	 *
	 * @return bool
	 */
	public function should_restrict_query( WP_Query &$query ) {
		if ( empty( $this->taxonomies->get_restricting_taxonomies( $query->get( 'post_type' ) ) ) ) {
			return false;
		}

		if ( ! $this->queries->should_restrict_queries() ) {
			return false;
		}
		if ( ! $this->queries->should_restrict_query( $query ) ) {
			return false;
		}

		if ( ! $this->post_types->is_restricted_post_type( $query->get( 'post_type' ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @param WP_Query $query
	 */
	public function restrict_query( WP_Query &$query ) {
		$post_types             = $query->get( 'post_type' );
		$restricting_taxonomies = $this->taxonomies->get_restricting_taxonomies( $post_types );

		$queried_restricted_post_types   = $this->post_types->get_restricted_post_types_in( $post_types );
		$queried_unrestricted_post_types = array_diff( $post_types, $queried_restricted_post_types );
		if ( $queried_unrestricted_post_types ) {
			$this->add_excluded_post_ids_to( $query, $restricting_taxonomies, $queried_restricted_post_types );
		} else {
			$this->add_filtering_tax_query_to( $query, $restricting_taxonomies );
		}
	}

	/**
	 * @param trc_Core_PostTypesInterface $post_types
	 */
	public function set_post_types( trc_Core_PostTypesInterface $post_types ) {
		$this->post_types = $post_types;
	}

	/**
	 * @param trc_Core_RestrictingTaxonomiesInterface $taxonomies
	 */
	public function set_taxonomies( trc_Core_RestrictingTaxonomiesInterface $taxonomies ) {
		$this->taxonomies = $taxonomies;
	}

	/**
	 * @param trc_Core_FilteringTaxQueryGeneratorInterface $filtering_taxonomy
	 */
	public function set_filtering_taxonomy_generator( trc_Core_FilteringTaxQueryGeneratorInterface $filtering_taxonomy ) {
		$this->filtering_taxonomy_generator = $filtering_taxonomy;
	}

	/**
	 * @param trc_Core_QueriesInterface $queries
	 */
	public function set_queries( trc_Core_QueriesInterface $queries ) {
		$this->queries = $queries;
	}

	/**
	 * @param WP_Query $query
	 * @param          $restricting_taxonomies
	 */
	protected function add_filtering_tax_query_to( WP_Query &$query, array $restricting_taxonomies ) {
		foreach ( $restricting_taxonomies as $restricting_tax_name ) {
			$query->tax_query->queries[] = $this->filtering_taxonomy_generator->get_tax_query_for( $restricting_tax_name );
		}
		$query->query_vars['tax_query'] = $query->tax_query->queries;
	}

	/**
	 * @param WP_Query $query
	 * @param array    $restricting_taxonomies
	 * @param array    $post_types
	 */
	protected function add_excluded_post_ids_to( WP_Query &$query, array $restricting_taxonomies, array $post_types ) {
		$excluded_query = trc_Core_Query::instance( [
			'post_type'        => $post_types,
			'suppress_filters' => true,
			'nopaging'         => true,
			'fields'           => 'ids'
		] );

		$this->add_filtering_tax_query_to( $excluded_query, $restricting_taxonomies );

		$excluded_ids = $excluded_query->get_posts();

		if ( empty( $excluded_ids ) ) {
			return;
		}

		$post__not_in = array_unique( array_merge( $query->get( 'post__not_in', array() ), $excluded_ids ) );
		$query->set( 'post__not_in', $post__not_in );
	}
}