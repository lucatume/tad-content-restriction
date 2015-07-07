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
	 * @return trc_Core_QueryScrutinizer
	 */
	public static function instance() {
		$instance = new self();

		$instance->post_types             = trc_Core_Plugin::instance()->post_types;
		$instance->restricting_taxonomies = trc_Core_Plugin::instance()->taxonomies;

		return $instance;
	}

	/**
	 * @param WP_Query $query
	 */
	public function set_query( WP_Query &$query ) {
		$this->query = $query;

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
	 * @param trc_Core_RestrictingTaxonomiesInterface $restricting_taxonomies
	 */
	public function set_restricting_taxonomies( trc_Core_RestrictingTaxonomiesInterface $restricting_taxonomies ) {
		$this->restricting_taxonomies = $restricting_taxonomies;
	}

	/**
	 * @return bool
	 */
	public function is_mixed_restriction_query() {
		if ( count( $this->queried_restricted_post_types ) == 0 ) {
			return false;
		}

		if ( count( $this->queried_unrestricted_post_types ) > 0 ) {
			return true;
		}

		$restricting_taxonomies       = $this->restricting_taxonomies->get_restricting_taxonomies();
		$restricting_taxonomies_count = count( $restricting_taxonomies );

		foreach ( $this->queried_restricted_post_types as $queried_restricted_post_type ) {
			$post_type_restricting_taxonomies = $this->restricting_taxonomies->get_restricting_taxonomies_for( $queried_restricted_post_type );


			if ( count( array_intersect( $restricting_taxonomies, $post_type_restricting_taxonomies ) ) < $restricting_taxonomies_count ) {
				return true;
			}
		}
	}

	public function scrutinize() {
		if ( empty( $this->query ) ) {
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

	public function is_querying_restricted_post_types() {
		$querying_restricted_post_types = count( $this->queried_restricted_post_types ) > 0;
		if ( $querying_restricted_post_types ) {
			$applied_restricting_taxonomy_count = 0;
			foreach ( $this->queried_restricted_post_types as $post_type ) {
				$applied_restricting_taxonomy_count += count( $this->restricting_taxonomies->get_restricting_taxonomies_for( $post_type ) );
			}

			$querying_restricted_post_types = $applied_restricting_taxonomy_count > 0;
		}

		return $querying_restricted_post_types;
	}
}