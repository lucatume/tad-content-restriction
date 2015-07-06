<?php


class trc_Core_ExcludedPostsQuery implements trc_Core_ExcludedPostsQueryInterface {

	/**
	 * @var trc_Core_FilteringTaxQueryGeneratorInterface
	 */
	protected $filtering_tax_query_generator;

	public static function instance() {
		$instance                                = new self;
		$instance->filtering_tax_query_generator = trc_Core_FilteringTaxQueryGenerator::instance();

		return $instance;
	}

	public function get_excluded_posts( $restricting_taxonomies, array $queried_restricted_post_types ) {
		/** @var \wpdb $wpdb */
		global $wpdb;

		$unaccessbile_restricted_ids = array();

		foreach ( $restricting_taxonomies as $restricting_taxonomy ) {
			$tax_query     = new WP_Tax_Query( array( $this->filtering_tax_query_generator->get_tax_query_for( $restricting_taxonomy, false ) ) );
			$sql           = $tax_query->get_sql( $wpdb->posts, 'ID' );
			$post_types_in = "('" . implode( "','", $queried_restricted_post_types ) . "')";
			$where         = "WHERE {$wpdb->posts}.post_type IN $post_types_in {$sql['where']}";
			$query         = "SELECT ID from {$wpdb->posts} {$sql['join']} {$where}";
			$ids           = $wpdb->get_col( $query );

			$unaccessbile_restricted_ids = array_merge( $unaccessbile_restricted_ids, $ids );
		}

		return $unaccessbile_restricted_ids;
	}

	/**
	 * @param trc_Core_FilteringTaxQueryGeneratorInterface $filtering_tax_query_generator
	 */
	public function set_filtering_tax_query_generator( $filtering_tax_query_generator ) {
		$this->filtering_tax_query_generator = $filtering_tax_query_generator;
	}
}