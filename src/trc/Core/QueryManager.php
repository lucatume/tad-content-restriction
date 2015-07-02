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
	protected $auxiliary_queries = array();

	/**
	 * @var trc_Core_FilteringTaxQueryGeneratorInterface
	 */
	protected $filtering_tax_query_generator;

	/**
	 * @param WP_Query $query
	 */
	public function set_query( WP_Query $query ) {
		$this->original_query    = $this->main_query = $query;
		$this->auxiliary_queries = array();

		$queried_post_types = $query->get( 'post_type' );
		$queried_post_types = is_array( $queried_post_types ) ? $queried_post_types : array( $queried_post_types );

		$queried_restricted_post_types = $this->post_types->get_restricted_post_types_in( $queried_post_types );
		if ( empty( $queried_restricted_post_types ) ) {
			return $this;
		}

		$queried_unrestricted_post_types = array_values( array_diff( $queried_post_types, $queried_restricted_post_types ) );
		if ( $queried_unrestricted_post_types ) {
			$this->main_query->set( 'post_type', $queried_unrestricted_post_types );
			$restricting_taxonomies = $this->restricting_taxonomies->get_restricting_taxonomies();
			foreach ( $restricting_taxonomies as $restricting_taxonomy ) {
				$this->auxiliary_queries['post__not_in'][] = trc_Core_FastIDQuery::instance( array(
					'post_type' => $queried_restricted_post_types,
					'tax_query' => $this->filtering_tax_query_generator->get_tax_query_for( $restricting_taxonomy )
				) );
			}
		}

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

	/**
	 * @param string|null $key The WP_Query query var the auxiliary queries should update
	 *
	 * @return trc_Core_QueryInterface[]
	 */
	public function get_auxiliary_queries( $key = null ) {
		return ( $key && isset( $this->auxiliary_queries[ $key ] ) ) ? $this->auxiliary_queries[ $key ] : $this->auxiliary_queries;
	}

	/**
	 * @param trc_Core_FilteringTaxQueryGeneratorInterface $filtering_tax_query_generator
	 */
	public function set_filtering_tax_query_generator( trc_Core_FilteringTaxQueryGeneratorInterface $filtering_tax_query_generator ) {
		$this->filtering_tax_query_generator = $filtering_tax_query_generator;
	}
}