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
	 * @var trc_Core_QueriesInterface
	 */
	protected $queries;

	/**
	 * @var trc_Core_QueryScrutinizerInterface
	 */
	protected $query_scrutinizer;

	/**
	 * @var trc_Core_QueryMarshalInterface
	 */
	protected $query_marshal;

	public static function instance() {
		$instance = new self;

		$instance->taxonomies        = trc_Core_Plugin::instance()->taxonomies;
		$instance->post_types        = trc_Core_Plugin::instance()->post_types;
		$instance->queries           = trc_Core_Queries::instance();
		$instance->query_scrutinizer = trc_Core_QueryScrutinizer::instance();
		$instance->query_marshal     = trc_Core_QueryMarshal::instance();

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

		$this->stop_filtering();

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
		$this->query_scrutinizer->set_query( $query );
		$this->query_scrutinizer->scrutinize();

		if ( $this->query_scrutinizer->is_querying_restricted_post_types() ) {
			$this->query_marshal->set_query( $query );
			$this->query_marshal->set_query_scrutinizer( $this->query_scrutinizer );
			if ( $this->query_scrutinizer->is_mixed_restriction_query() ) {
				$this->query_marshal->set_excluded_posts();
			} else {
				$this->query_marshal->set_filtering_tax_query();
			}
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
	 * @param trc_Core_QueriesInterface $queries
	 */
	public function set_queries( trc_Core_QueriesInterface $queries ) {
		$this->queries = $queries;
	}


	/**
	 * @param trc_Core_QueryScrutinizerInterface $query_scrutinizer
	 */
	public function set_query_scrutinizer( trc_Core_QueryScrutinizerInterface $query_scrutinizer ) {
		$this->query_scrutinizer = $query_scrutinizer;
	}

	/**
	 * @param trc_Core_QueryMarshalInterface $query_marshal
	 */
	public function set_query_marshal( $query_marshal ) {
		$this->query_marshal = $query_marshal;
	}

	public function stop_filtering() {
		remove_action( 'pre_get_posts', array( $this, 'maybe_restrict_query' ), 10 );
	}
}