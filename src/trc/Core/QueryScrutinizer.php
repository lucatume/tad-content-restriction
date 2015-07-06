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
		$this->query                       = $query;

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
		$queried_count = count( $this->queried_post_types );

		return $queried_count != count( $this->queried_unrestricted_post_types ) && $queried_count != count( $this->queried_restricted_post_types );
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
		return count( $this->queried_restricted_post_types ) > 0;
	}
}