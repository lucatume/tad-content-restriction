<?php


class trc_Core_RestrictingTaxonomies implements trc_Core_RestrictingTaxonomiesInterface {

	/**
	 * @var string[]  An array containing the registered restricting taxonomies.
	 */
	protected $taxonomies = array();

	public static function instance() {
		return new self();
	}

	public function get_restricting_taxonomies_for( $post_type ) {
		$post_types = is_array( $post_type ) ? $post_type : array( $post_type );

		$taxonomies = $this->get_taxonomies_for_post_types( $post_types );

		return apply_filters( 'trc_post_type_restricting_taxonomies', $taxonomies, $post_types );
	}

	public function add( $taxonomy ) {
		if ( in_array( $taxonomy, $this->taxonomies ) ) {
			return $this;
		}
		$this->taxonomies[] = $taxonomy;

		return $this;
	}

	public function remove( $taxonomy ) {
		$this->taxonomies = array_diff( $this->taxonomies, $taxonomy );

		return $this;
	}

	/**
	 * @return array An array of restricting taxonomies
	 */
	public function get_restricting_taxonomies() {
		return apply_filters( 'trc_restricting_taxonomies', $this->taxonomies );
	}

	/**
	 * @param array $post_types
	 *
	 * @return array
	 */
	protected function get_taxonomies_for_post_types( array $post_types ) {
		global $wp_taxonomies;
		$taxonomies = array();

		foreach ( $wp_taxonomies as $taxonomy ) {
			if ( empty( array_intersect( $post_types, $taxonomy->object_type ) ) ) {
				continue;
			}
			$taxonomies[] = $taxonomy->name;
		}

		return array_intersect( $this->taxonomies, $taxonomies );
	}
}