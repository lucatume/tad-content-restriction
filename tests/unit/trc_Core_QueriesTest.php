<?php

use tad\FunctionMocker\FunctionMocker as Test;

class trc_Core_QueriesTest extends \PHPUnit_Framework_TestCase {

	protected function setUp() {
		Test::setUp();

		Test::replace( 'apply_filters', function ( $tag, $val ) {
			return $val;
		} );
	}

	protected function tearDown() {
		Test::tearDown();
	}

	/**
	 * @test
	 * it should allow filtering whether queries should be restricted or not
	 */
	public function it_should_allow_filtering_whether_queries_should_be_restricted_or_not() {
		Test::replace( 'apply_filters', function ( $tag, $val ) {
			return $tag == 'trc_should_restrict_queries' ? true : $val;
		} );

		$sut = new trc_Core_Queries();

		Test::assertTrue( $sut->should_restrict_queries() );

		Test::replace( 'apply_filters', function ( $tag, $val ) {
			return $tag == 'trc_should_restrict_queries' ? false : $val;
		} );

		Test::assertFalse( $sut->should_restrict_queries() );
	}

	/**
	 * @test
	 * it should allow setting query var to restrict query
	 */
	public function it_should_allow_setting_query_var_to_restrict_query() {
		$sut = new trc_Core_Queries();

		$query = Test::replace( 'WP_Query' )->method( 'get', function ( $key ) {
			return $key == 'no_restriction' ? true : 'foo';
		} )->get();

		Test::assertFalse( $sut->should_restrict_query( $query ) );
	}

	/**
	 * @test
	 * it should restrict the query if no_restriction query var falsy
	 */
	public function it_should_restrict_the_query_if_no_restriction_query_var_falsy() {
		$sut = new trc_Core_Queries();

		$query = Test::replace( 'WP_Query' )->method( 'get', function ( $key ) {
			return $key == 'no_restriction' ? null : 'foo';
		} )->get();

		Test::assertTrue( $sut->should_restrict_query( $query ) );
	}

	/**
	 * @test
	 * it should allow filtering whether a query should be restricted or not
	 */
	public function it_should_allow_filtering_whether_a_query_should_be_restricted_or_not() {
		$sut = new trc_Core_Queries();

		$apply_filters = Test::replace( 'apply_filters', function ( $tag, $val ) {
			return $tag == 'trc_should_restrict_query' ? false : $val;
		} );

		$query = Test::replace( 'WP_Query' )->get();

		$should_restrict_query = $sut->should_restrict_query( $query );

		Test::assertFalse( $should_restrict_query );

		$apply_filters->wasCalledWithOnce( [ 'trc_should_restrict_query', true, $query ] );
	}
}
