<?php


interface trc_Core_RestrictingTaxonomiesInterface {

	/**
	 * @return trc_Core_RestrictingTaxonomiesInterface
	 */
	public static function instance();

	/**
	 * @return array An array of restricting taxonomies
	 */
	public function get_restricting_taxonomies();

	/**
	 * @param string|array $post_type
	 *
	 * @return array
	 */
	public function get_restricting_taxonomies_for( $post_type );

	/**
	 * @param sring $taxonomy
	 *
	 * @return trc_Core_RestrictingTaxonomiesInterface
	 */
	public function add( $taxonomy );

	/**
	 * @param string $taxonomy
	 *
	 * @return trc_Core_RestrictingTaxonomiesInterface
	 */
	public function remove( $taxonomy );
}