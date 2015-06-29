<?php


class trc_Queries implements trc_QueriesInterface {

	public static function instance() {
		return new self;
	}

	public function should_restrict_queries() {
		return apply_filters( 'trc_should_restrict_queries', true );
	}

	public function should_restrict_query( WP_Query $query ) {
		$should_be_restricted = ! $query->get( 'no_restriction', false );

		return apply_filters( 'trc_should_restrict_query', $should_be_restricted, $query );
	}


}