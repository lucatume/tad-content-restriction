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

		$taxonomies = Test::replace( 'trc_Core_RestrictingTaxonomies' )->method( 'get_restricting_taxonomies', [ ] )->get();
		$sut->set_taxonomies( $taxonomies );

		Test::assertFalse( $sut->should_restrict_query( $this->get_mock_query() ) );
	}

	private function get_mock_query() {
		return Test::replace( 'WP_Query' )->get();
	}

	/**
	 * @test
	 * it should not restrict the query if queries are not to be restricted
	 */
	public function it_should_not_restrict_the_query_if_queries_are_not_to_be_restricted() {
		$sut = new trc_Core_QueryRestrictor();

		$taxonomies = Test::replace( 'trc_Core_RestrictingTaxonomies' )->method( 'get_restricting_taxonomies', [ 'tax_a' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		$queries = Test::replace( 'trc_Core_Queries' )->method( 'should_restrict_queries', false )->get();
		$sut->set_queries( $queries );

		Test::assertFalse( $sut->should_restrict_query( $this->get_mock_query() ) );
	}

	/**
	 * @test
	 * it should not restrict the query if the query is not to be restricted
	 */
	public function it_should_not_restrict_the_query_if_the_query_is_not_to_be_restricted() {
		$sut = new trc_Core_QueryRestrictor();

		$taxonomies = Test::replace( 'trc_Core_RestrictingTaxonomies' )->method( 'get_restricting_taxonomies', [ 'tax_a' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		$queries = Test::replace( 'trc_Core_Queries' )->method( 'should_restrict_queries', true )
		               ->method( 'should_restrict_query', false )->get();
		$sut->set_queries( $queries );

		Test::assertFalse( $sut->should_restrict_query( $this->get_mock_query() ) );
	}

	/**
	 * @test
	 * it should not restrict the query if the post type is not a restricted post type
	 */
	public function it_should_not_restrict_the_query_if_the_post_type_is_not_a_restricted_post_type() {
		$sut = new trc_Core_QueryRestrictor();

		$taxonomies = Test::replace( 'trc_Core_RestrictingTaxonomies' )->method( 'get_restricting_taxonomies', [ 'tax_a' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		$queries = Test::replace( 'trc_Core_Queries' )->method( 'should_restrict_queries', true )
		               ->method( 'should_restrict_query', true )->get();
		$sut->set_queries( $queries );

		$post_types = Test::replace( 'trc_Core_PostTypes' )->method( 'is_restricted_post_type', false )->get();
		$sut->set_post_types( $post_types );

		Test::assertFalse( $sut->should_restrict_query( $this->get_mock_query() ) );
	}

	/**
	 * @test
	 * it should add a restricting tax query if one restricting taxonomy is present
	 */
	public function it_should_add_a_restricting_tax_query_if_one_restricting_taxonomy_is_present() {
		$sut = new trc_Core_QueryRestrictor();

		$taxonomies = Test::replace( 'trc_Core_RestrictingTaxonomies' )->method( 'get_restricting_taxonomies', [ 'tax_a' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		$filtering_taxonomy = Test::replace( 'trc_Core_FilteringTaxQueryGenerator' )->method( 'get_tax_query_for', 'foo' )->get();
		$sut->set_filtering_taxonomy( $filtering_taxonomy );

		$query                     = $this->get_mock_query();
		$query->tax_query          = new stdClass();
		$query->tax_query->queries = [ ];

		$sut->restrict_query( $query );

		Test::assertEquals( array( 'foo' ), $query->tax_query->queries );
	}

	/**
	 * @test
	 * it should add a restricting tax query for each restricting taxonomy
	 */
	public function it_should_add_a_restricting_tax_query_for_each_restricting_taxonomy() {
		$sut = new trc_Core_QueryRestrictor();

		$taxonomies = Test::replace( 'trc_Core_RestrictingTaxonomies' )->method( 'get_restricting_taxonomies', [ 'tax_a', 'tax_b' ] )
		                  ->get();
		$sut->set_taxonomies( $taxonomies );

		$filtering_taxonomy = Test::replace( 'trc_Core_FilteringTaxQueryGenerator' )->method( 'get_tax_query_for', 'foo' )->get();
		$sut->set_filtering_taxonomy( $filtering_taxonomy );

		$query                     = $this->get_mock_query();
		$query->tax_query          = new stdClass();
		$query->tax_query->queries = [ ];

		$sut->restrict_query( $query );

		Test::assertEquals( array( 'foo', 'foo' ), $query->tax_query->queries );
	}

	/**
	 * @test
	 * it should leave previous tax queries in place when adding restricting tax queries
	 */
	public function it_should_leave_previous_tax_queries_in_place_when_adding_restricting_tax_queries() {
		$sut = new trc_Core_QueryRestrictor();

		$taxonomies = Test::replace( 'trc_Core_RestrictingTaxonomies' )->method( 'get_restricting_taxonomies', [ 'tax_a', 'tax_b' ] )
		                  ->get();
		$sut->set_taxonomies( $taxonomies );

		$filtering_taxonomy = Test::replace( 'trc_Core_FilteringTaxQueryGenerator' )->method( 'get_tax_query_for', 'foo' )->get();
		$sut->set_filtering_taxonomy( $filtering_taxonomy );

		$query                     = $this->get_mock_query();
		$query->tax_query          = new stdClass();
		$query->tax_query->queries = [ 'here before' ];

		$sut->restrict_query( $query );

		Test::assertEquals( array( 'here before', 'foo', 'foo' ), $query->tax_query->queries );
	}

}