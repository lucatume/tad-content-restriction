<?php


class trc_Taxonomies implements trc_RestrictingTaxonomiesInterface {

	/**
	 * @var string[]  An array containing the registered restricting taxonomies.
	 */
	protected $taxonomies = array();

	public static function instance() {
		return new self();
	}

	public function get_restricting_taxonomies( $post_type ) {
		$post_types = is_array( $post_type ) ? $post_type : array( $post_type );
		$taxonomies = array_intersect( $this->taxonomies, array_keys( get_taxonomies( array( 'object_type' => $post_types ) ) ) );

		return apply_filters( 'trc_restricting_taxonomies', $taxonomies, $post_types );
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
}