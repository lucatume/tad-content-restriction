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
	protected $filtering_taxonomy;

	/**
	 * @var trc_Core_QueriesInterface
	 */
	protected $queries;

	public static function instance() {
		$instance = new self;

		$instance->taxonomies         = trc_Core_Plugin::instance()->taxonomies;
		$instance->post_types         = trc_Core_Plugin::instance()->post_types;
		$instance->filtering_taxonomy = trc_Core_FilteringTaxQueryGenerator::instance();
		$instance->queries            = trc_Core_Queries::instance();

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
		$restricting_taxonomies = $this->taxonomies->get_restricting_taxonomies( $query->get( 'post_type' ) );

		foreach ( $restricting_taxonomies as $restricting_tax_name ) {
			$query->tax_query->queries[] = $this->filtering_taxonomy->get_tax_query_for( $restricting_tax_name );
		}
		$query->query_vars['tax_query'] = $query->tax_query->queries;
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
	public function set_filtering_taxonomy( trc_Core_FilteringTaxQueryGeneratorInterface $filtering_taxonomy ) {
		$this->filtering_taxonomy = $filtering_taxonomy;
	}

	/**
	 * @param trc_Core_QueriesInterface $queries
	 */
	public function set_queries( trc_Core_QueriesInterface $queries ) {
		$this->queries = $queries;
	}
}