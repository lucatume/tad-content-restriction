<?php


class trc_QueryRestrictor {

	/**
	 * @var trc_User
	 */
	protected $user;

	/**
	 * @var trc_PostTypes
	 */
	protected $post_types;

	/**
	 * @var trc_Taxonomies
	 */
	protected $taxonomies;

	public static function instance() {
		$instance = new self;

		$instance->post_types = trc_PostTypes::instance();
		$instance->taxonomies = trc_Taxonomies::instance();
		$instance->user       = trc_User::instance();

		return $instance;
	}

	public function init() {
		if ( is_admin() ) {
			// restrictions will not apply to back-end
			return;
		}
		add_action( 'pre_get_posts', array( $this, 'maybe_restrict_query' ) );
	}

	public function maybe_restrict_query( WP_Query &$query ) {
		if ( ! $this->should_be_restricted( $query ) ) {
			return;
		}
		if ( ! $this->is_restricted_post_type( $query ) ) {
			return;
		}

		if ( current_user_can( 'edit_others_posts' ) ) {
			return $query;
		}

		$taxonomies = $this->get_restricting_taxonomies();

		if ( empty( $taxonomies ) ) {
			return;
		}

		foreach ( $taxonomies as $restricting_tax_name ) {
			$tax_query                   = [
				[
					'taxonomy' => $restricting_tax_name,
					'field'    => 'slug',
					'terms'    => $this->user->get_content_access_slugs(),
					'operator' => 'IN'
				]
			];
			$query->tax_query->queries[] = $tax_query;
		}
		$query->query_vars['tax_query'] = $query->tax_query->queries;

		return $query;
	}

	protected function should_be_restricted( WP_Query &$query ) {
		$no_restriction = $query->get( 'no_restriction', false ) == true;

		return apply_filters( 'trc_should_be_restricted', $no_restriction, $query );
	}

	protected function is_restricted_post_type( WP_Query $query ) {
		$is_restricted_post_type = count( array_intersect( $query->get( 'post_type' ), $this->get_restricted_post_types() ) ) > 0;

		return apply_filters( 'trc_is_restricted_post_type', $is_restricted_post_type, $query );
	}

	protected function get_restricted_post_types() {
		return $this->post_types->get_restricted_post_types();
	}

	protected function get_restricting_taxonomies() {
		return $this->taxonomies->get_restricting_taxonomies();
	}
}