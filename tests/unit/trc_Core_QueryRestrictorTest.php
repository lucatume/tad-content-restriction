<?php

use tad\FunctionMocker\FunctionMocker as Test;

class trc_Core_QueryRestrictorTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		Test::setUp();
	}

	public function tearDown() {
		Test::tearDown();
	}

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		Test::assertInstanceOf( 'trc_Core_QueryRestrictor', new trc_Core_QueryRestrictor() );
	}

	/**
	 * @test
	 * it should not restrict the query if there are no restricting taxonomies
	 */
	public function it_should_not_restrict_the_query_if_there_are_no_restricting_taxonomies() {
		$sut = new trc_Core_QueryRestrictor();

		$taxonomies = Test::replace( 'trc_Core_RestrictingTaxonomies' )
		                  ->method( 'get_restricting_taxonomies_for', [ ] )
		                  ->get();
		$sut->set_taxonomies( $taxonomies );

		Test::assertFalse( $sut->should_restrict_query( $this->get_mock_query() ) );
	}

	private function get_mock_query() {
		return Test::replace( 'WP_Query' )
		           ->get();
	}

	/**
	 * @test
	 * it should not restrict the query if queries are not to be restricted
	 */
	public function it_should_not_restrict_the_query_if_queries_are_not_to_be_restricted() {
		$sut = new trc_Core_QueryRestrictor();

		$taxonomies = Test::replace( 'trc_Core_RestrictingTaxonomies' )
		                  ->method( 'get_restricting_taxonomies_for', [ 'tax_a' ] )
		                  ->get();
		$sut->set_taxonomies( $taxonomies );

		$queries = Test::replace( 'trc_Core_Queries' )
		               ->method( 'should_restrict_queries', false )
		               ->get();
		$sut->set_queries( $queries );

		Test::assertFalse( $sut->should_restrict_query( $this->get_mock_query() ) );
	}

	/**
	 * @test
	 * it should not restrict the query if the query is not to be restricted
	 */
	public function it_should_not_restrict_the_query_if_the_query_is_not_to_be_restricted() {
		$sut = new trc_Core_QueryRestrictor();

		$taxonomies = Test::replace( 'trc_Core_RestrictingTaxonomies' )
		                  ->method( 'get_restricting_taxonomies_for', [ 'tax_a' ] )
		                  ->get();
		$sut->set_taxonomies( $taxonomies );

		$queries = Test::replace( 'trc_Core_Queries' )
		               ->method( 'should_restrict_queries', true )
		               ->method( 'should_restrict_query', false )
		               ->get();
		$sut->set_queries( $queries );

		Test::assertFalse( $sut->should_restrict_query( $this->get_mock_query() ) );
	}

	/**
	 * @test
	 * it should not restrict the query if the post type is not a restricted post type
	 */
	public function it_should_not_restrict_the_query_if_the_post_type_is_not_a_restricted_post_type() {
		$sut = new trc_Core_QueryRestrictor();

		$taxonomies = Test::replace( 'trc_Core_RestrictingTaxonomies' )
		                  ->method( 'get_restricting_taxonomies_for', [ 'tax_a' ] )
		                  ->get();
		$sut->set_taxonomies( $taxonomies );

		$queries = Test::replace( 'trc_Core_Queries' )
		               ->method( 'should_restrict_queries', true )
		               ->method( 'should_restrict_query', true )
		               ->get();
		$sut->set_queries( $queries );

		$post_types = Test::replace( 'trc_Core_PostTypes' )
		                  ->method( 'is_restricted_post_type', false )
		                  ->get();
		$sut->set_post_types( $post_types );

		Test::assertFalse( $sut->should_restrict_query( $this->get_mock_query() ) );
	}

	/**
	 * @test
	 * it should not modify the query if not querying for any restricted post type
	 */
	public function it_should_not_modify_the_query_if_not_querying_for_any_restricted_post_type() {
		$sut = new trc_Core_QueryRestrictor();

		$query       = Test::replace( 'WP_Query' )
		                   ->get();
		$scrutinizer = Test::replace( 'trc_Core_QueryScrutinizerInterface' )
		                   ->method( 'is_querying_restricted_post_types', false )
		                   ->get();
		$marshal     = Test::replace( 'trc_Core_QueryMarshalInterface' )
		                   ->method( 'set_query' )
		                   ->get();

		$sut->set_query_scrutinizer( $scrutinizer );
		$sut->set_query_marshal( $marshal );

		$sut->restrict_query( $query );

		$marshal->wasNotCalled( 'set_query' );
	}

	public function isMixedQuery() {
		return [
			[ false ],
			[ true ]
		];
	}

	/**
	 * @test
	 * it should marshal the query if querying for restricted post types
	 * @dataProvider isMixedQuery
	 */
	public function it_should_marshal_the_query_if_querying_for_restricted_post_types( $mixed ) {
		$sut = new trc_Core_QueryRestrictor();

		$query       = Test::replace( 'WP_Query' )
		                   ->get();
		$scrutinizer = Test::replace( 'trc_Core_QueryScrutinizerInterface' )
		                   ->method( 'is_querying_restricted_post_types', true )
		                   ->method( 'is_mixed_restriction_query', $mixed )
		                   ->get();
		$marshal     = Test::replace( 'trc_Core_QueryMarshalInterface' )
		                   ->method( 'set_query' )
		                   ->get();

		$sut->set_query_scrutinizer( $scrutinizer );
		$sut->set_query_marshal( $marshal );

		$sut->restrict_query( $query );

		$marshal->wasCalledWithOnce( [ $query ], 'set_query' );
	}

	public function restrictedPostTypesCombos() {
		return [
			[ true, 'set_excluded_posts' ],
			[ false, 'set_filtering_tax_query' ]
		];
	}

	/**
	 * @test
	 * it should properly marshal the query
	 * @dataProvider restrictedPostTypesCombos
	 */
	public function it_should_properly_marshal_the_query( $mixed, $method ) {
		$sut = new trc_Core_QueryRestrictor();

		$query       = Test::replace( 'WP_Query' )
		                   ->get();
		$scrutinizer = Test::replace( 'trc_Core_QueryScrutinizerInterface' )
		                   ->method( 'is_querying_restricted_post_types', true )
		                   ->method( 'is_mixed_restriction_query', $mixed )
		                   ->get();
		$marshal     = Test::replace( 'trc_Core_QueryMarshalInterface' )
		                   ->method( $method )
		                   ->get();

		$sut->set_query_scrutinizer( $scrutinizer );
		$sut->set_query_marshal( $marshal );

		$sut->restrict_query( $query );

		$marshal->wasCalledOnce( $method );
	}
}