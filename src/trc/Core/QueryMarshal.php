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

	/**
	 * @var trc_Core_QueryInterface
	 */
	protected $set_id_query;

	/**
	 * @var trc_Core_QueryInterface
	 */
	protected $id_query;

	public static function instance() {
		$instance                                = new self;
		$instance->restricting_taxonomies        = trc_Core_Plugin::instance()->taxonomies;
		$instance->filtering_tax_query_generator = trc_Core_FilteringTaxQueryGenerator::instance();
		$instance->id_query                      = trc_Core_IDQuery::instance();

		return $instance;
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
		$post_types = $this->query_scrutinizer->get_queried_restricted_post_types();
		foreach ( $post_types as $post_type ) {
			$tax_queries = array();
			$taxonomies  = $this->restricting_taxonomies->get_restricting_taxonomies_for( $post_type );

			foreach ( $taxonomies as $tax ) {
				$tax_queries[] = $this->filtering_tax_query_generator->get_tax_query_for( $tax, false );
			}

			if ( empty( $tax_queries ) ) {
				continue;
			}

			$args = array( 'post_type' => $post_type, 'tax_query' => $tax_queries );

			$excluded_ids = $this->id_query->set_args( $args )
			                               ->get_posts();

			if ( empty( $excluded_ids ) ) {
				continue;
			}

			$this->query->set( 'post__not_in', array_merge( $this->query->get( 'post__not_in', array() ), $excluded_ids ) );
		}
	}

	public function set_filtering_tax_query() {
		$post_types = $this->query_scrutinizer->get_queried_restricted_post_types();
		$taxonomies = $this->restricting_taxonomies->get_restricting_taxonomies_for( $post_types );

		$this->prime_tax_query();

		foreach ( $taxonomies as $tax ) {
			$this->query->tax_query->queries[] = $this->filtering_tax_query_generator->get_tax_query_for( $tax );
		}

		$this->query->set( 'tax_query', $this->query->tax_query->queries );
	}

	public function set_id_query( trc_Core_QueryInterface $id_query ) {
		$this->id_query = $id_query;
	}

	protected function prime_tax_query() {
		if ( empty( $this->query->tax_query ) ) {
			$this->query->tax_query = new stdClass();
		}
		if ( empty( $this->query->tax_query->queries ) ) {
			$this->query->tax_query->queries = array();
		}
	}
}