<?php


class trc_QueryVars {

	public static function instance() {
		return new self;
	}

	public function init() {
		add_filter( 'query_vars', array( $this, 'query_vars' ) );
	}

	public function query_vars( array $vars ) {
		$vars[] = 'no_restriction';

		return $vars;
	}
}