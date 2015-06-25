<?php


class trc_QueryRestrictor {

	/**
	 * @var trc_PostTypes
	 */
	protected $post_types;

	/**
	 * @var trc_Taxonomies
	 */
	protected $taxonomies;

	/**
	 * @var trc_FilteringTaxonomy
	 */
	protected $filtering_taxonomy;

	public static function instance() {
		$instance = new self;

		$instance->post_types         = trc_PostTypes::instance();
		$instance->taxonomies         = trc_Taxonomies::instance();
		$instance->filtering_taxonomy = trc_FilteringTaxonomy::instance();

		return $instance;
	}

	public function init() {
		if ( is_admin() ) {
			// restrictions will not apply to back-end
			return $this;
		}
		add_action( 'pre_get_posts', array( $this, 'maybe_restrict_query' ) );

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
		if ( ! $this->should_be_restricted( $query ) ) {
			return false;
		}

		if ( ! $this->is_restricted_post_type( $query ) ) {
			return false;
		}

		if ( current_user_can( 'edit_others_posts' ) ) {
			return false;
		}

		$taxonomies = $this->get_restricting_taxonomies();

		if ( empty( $taxonomies ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @param WP_Query $query
	 */
	public function restrict_query( WP_Query &$query ) {
		$restricting_taxonomies = $this->get_restricting_taxonomies();

		foreach ( $restricting_taxonomies as $restricting_tax_name ) {
			$query->tax_query->queries[] = $this->filtering_taxonomy->get_array_for( $restricting_tax_name );
		}
		$query->query_vars['tax_query'] = $query->tax_query->queries;
	}

	protected function should_be_restricted( WP_Query &$query ) {
		$should_be_restricted = ! $query->get( 'no_restriction', false );

		return apply_filters( 'trc_should_restrict_query', $should_be_restricted, $query );
	}

	protected function is_restricted_post_type( WP_Query $query ) {
		$post_types = $query->get( 'post_type' );
		if ( empty( $post_types ) ) {
			return false;
		}
		$post_types              = is_array( $post_types ) ? $post_types : array( $post_types );
		$is_restricted_post_type = count( array_intersect( $post_types, $this->get_restricted_post_types() ) ) > 0;

		return apply_filters( 'trc_is_restricted_post_type', $is_restricted_post_type, $query );
	}

	protected function get_restricting_taxonomies() {
		return $this->taxonomies->get_restricting_taxonomies();
	}

	protected function get_restricted_post_types() {
		return $this->post_types->get_restricted_post_types();
	}

	/**
	 * @param trc_PostTypes $post_types
	 */
	public function set_post_types( trc_PostTypes $post_types ) {
		$this->post_types = $post_types;
	}

	/**
	 * @param trc_Taxonomies $taxonomies
	 */
	public function set_taxonomies( trc_Taxonomies $taxonomies ) {
		$this->taxonomies = $taxonomies;
	}

	/**
	 * @param trc_FilteringTaxonomy $filtering_taxonomy
	 */
	public function set_filtering_taxonomy( $filtering_taxonomy ) {
		$this->filtering_taxonomy = $filtering_taxonomy;
	}
}