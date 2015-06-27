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

	/**
	 * @var trc_Queries
	 */
	protected $queries;

	/**
	 * @var trc_User
	 */
	protected $user;

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
		if ( empty( $this->taxonomies->get_restricting_taxonomies() ) ) {
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

		if ( $this->user->can_access_query( $query ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @param WP_Query $query
	 */
	public function restrict_query( WP_Query &$query ) {
		$restricting_taxonomies = $this->taxonomies->get_restricting_taxonomies();

		foreach ( $restricting_taxonomies as $restricting_tax_name ) {
			$query->tax_query->queries[] = $this->filtering_taxonomy->get_array_for( $restricting_tax_name );
		}
		$query->query_vars['tax_query'] = $query->tax_query->queries;
	}

	/**
	 * @param trc_User $user
	 */
	public function set_user( trc_UserInterface $user ) {
		$this->user = $user;
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
	public function set_filtering_taxonomy( trc_FilteringTaxonomy $filtering_taxonomy ) {
		$this->filtering_taxonomy = $filtering_taxonomy;
	}

	/**
	 * @param trc_Queries $queries
	 */
	public function set_queries( trc_Queries $queries ) {
		$this->queries = $queries;
	}
}