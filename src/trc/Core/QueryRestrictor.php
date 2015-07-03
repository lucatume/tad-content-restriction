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

	/**
	 * @var array
	 */
	protected $unaccessible_restricted_ids;

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
		if ( empty( $this->taxonomies->get_restricting_taxonomies_for( $query->get( 'post_type' ) ) ) ) {
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
		$post_types             = is_array( $post_types ) ? $post_types : array( $post_types );
		$restricting_taxonomies = $this->taxonomies->get_restricting_taxonomies_for( $post_types );

		$query_manager = trc_Core_QueryManager::instance( $query )
		                                      ->analyze();

		if ( $query_manager->requires_splitting() ) {
			add_filter( 'posts_results', array( $this, 'remove_unaccessible_posts' ) );
			$this->unaccessible_restricted_ids = $query_manager->get_unaccessible_restricted_ids();
		} else {
			$this->add_filtering_tax_query_to( $query, $restricting_taxonomies );
		}
	}

	/**
	 * @param WP_Post[] $posts
	 */
	public function remove_unaccessible_posts( array $posts ) {
		remove_filter( 'posts_results', array( $this, 'remove_unaccessible_posts' ) );

		return array_filter( $posts, array( $this, 'is_accessible' ) );
	}

	protected function is_accessible( WP_Post $post ) {
		return ! in_array( $post->ID, $this->unaccessible_restricted_ids );
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
		$excluded_query = trc_Core_FastIDQuery::instance( [
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

		$post__in = array_unique( array_merge( $query->get( 'post__in', array() ), $excluded_ids ) );
		$query->set( 'post__in', $post__in );
	}
}