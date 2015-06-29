<?php


interface trc_RestrictingTaxonomiesInterface {

	public static function instance();

	public function get_restricting_taxonomies( $post_type );

	public function add( $taxonomy );

	public function remove( $taxonomy );
}