<?php


class trc_Taxonomies implements trc_RestrictingTaxonomiesInterface {

	public static function instance() {
		return new self();
	}

	public function get_restricting_taxonomies() {
		$taxonomies = trc_Plugin::instance()->restriction_taxonomy;

		return apply_filters( 'trc_restricting_taxonomies', $taxonomies );
	}
}