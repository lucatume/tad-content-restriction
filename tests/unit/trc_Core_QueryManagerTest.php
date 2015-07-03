<?php

use tad\FunctionMocker\FunctionMocker as Test;

class trc_Core_QueryManagerTest extends \PHPUnit_Framework_TestCase {

	protected function setUp() {
		Test::setUp();
	}

	protected function tearDown() {
		Test::tearDown();
	}

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		Test::assertInstanceOf( 'trc_Core_QueryManager', new trc_Core_QueryManager() );
	}

	/**
	 * @test
	 * it should return the original query if no restricted post types are queried in it
	 */
	public function it_should_return_the_original_query_if_no_restricted_post_types_are_queried_in_it() {
		$query      = Test::replace( 'WP_Query' )
		                  ->method( 'get', [ 'post' ] )
		                  ->get();
		$post_types = Test::replace( 'trc_Core_PostTypesInterface' )
		                  ->method( 'get_restricted_post_types', [ 'page' ] )
		                  ->method( 'get_restricted_post_types_in', [ ] )
		                  ->get();

		$sut = new trc_Core_QueryManager();
		$sut->set_post_types( $post_types );

		$sut->set_query( $query )
		    ->manage();

		Test::assertEquals( $query, $sut->get_main_query() );
	}

	/**
	 * @test
	 * it should return the same query if one restricted post type and one restricting taxonomy
	 */
	public function it_should_return_the_same_query_if_one_restricted_post_type_and_one_restricting_taxonomy() {
		$query      = Test::replace( 'WP_Query' )
		                  ->method( 'get', [ 'post' ] )
		                  ->get();
		$post_types = Test::replace( 'trc_Core_PostTypesInterface' )
		                  ->method( 'get_restricted_post_types', [ 'post' ] )
		                  ->method( 'get_restricted_post_types_in', [ 'post' ] )
		                  ->get();
		$taxonomies = Test::replace( 'trc_Core_RestrictingTaxonomiesInterface' )
		                  ->method( 'get_restricting_taxonomies_for', [ 'tax_1' ] )
		                  ->method( 'get_restricting_taxonomies', [ 'tax_1' ] )
		                  ->get();

		$sut = new trc_Core_QueryManager();
		$sut->set_post_types( $post_types );
		$sut->set_restricting_taxonomies( $taxonomies );

		$sut->set_query( $query )
		    ->manage();

		Test::assertEquals( $query, $sut->get_main_query() );
	}

	/**
	 * @test
	 * it should set post type on main query to unrestricted only if querying for one restricted and one unrestricted
	 * post type
	 */
	public function it_should_set_post_type_on_main_query_to_unrestricted_only_if_querying_for_one_restricted_and_one_unrestricted_post_type() {
		$query      = Test::replace( 'WP_Query' )
		                  ->method( 'get', [ 'post', 'page' ] )
		                  ->method( 'set' )
		                  ->get();
		$post_types = Test::replace( 'trc_Core_PostTypesInterface' )
		                  ->method( 'get_restricted_post_types', [ 'post' ] )
		                  ->method( 'get_restricted_post_types_in', [ 'post' ] )
		                  ->get();
		$taxonomies = Test::replace( 'trc_Core_RestrictingTaxonomiesInterface' )
		                  ->method( 'get_restricting_taxonomies_for', [ 'tax_1' ] )
		                  ->method( 'get_restricting_taxonomies', [ 'tax_1' ] )
		                  ->get();

		$filtering_tax_query_generator = Test::replace( 'trc_Core_FilteringTaxQueryGenerator' )
		                                     ->method( 'get_tax_query_for', 'tax_query' )
		                                     ->get();

		Test::replace( 'trc_Core_FastIDQuery::instance', new stdClass() );

		$sut = new trc_Core_QueryManager();
		$sut->set_post_types( $post_types );
		$sut->set_restricting_taxonomies( $taxonomies );
		$sut->set_filtering_tax_query_generator( $filtering_tax_query_generator );

		$sut->set_query( $query )
		    ->manage();

		$query->wasCalledWithOnce( [ 'post_type', [ 'page' ] ], 'set' );
	}

	/**
	 * @test
	 * it should create a subquery if querying for unrestricted and one restricted post type with one restriction tax
	 */
	public function it_should_create_a_subquery_if_querying_for_unrestricted_and_one_restricted_post_type_with_one_restriction_tax() {
		$query      = Test::replace( 'WP_Query' )
		                  ->method( 'get', [ 'post', 'page' ] )
		                  ->method( 'set' )
		                  ->get();
		$post_types = Test::replace( 'trc_Core_PostTypesInterface' )
		                  ->method( 'get_restricted_post_types', [ 'post' ] )
		                  ->method( 'get_restricted_post_types_in', [ 'post' ] )
		                  ->get();
		$taxonomies = Test::replace( 'trc_Core_RestrictingTaxonomiesInterface' )
		                  ->method( 'get_restricting_taxonomies_for', [ 'tax_1' ] )
		                  ->method( 'get_restricting_taxonomies', [ 'tax_1' ] )
		                  ->get();

		$filtering_tax_query_generator = Test::replace( 'trc_Core_FilteringTaxQueryGenerator' )
		                                     ->method( 'get_tax_query_for', 'tax_query' )
		                                     ->get();

		Test::replace( 'trc_Core_FastIDQuery::instance', new stdClass() );

		$sut = new trc_Core_QueryManager();
		$sut->set_post_types( $post_types );
		$sut->set_restricting_taxonomies( $taxonomies );
		$sut->set_filtering_tax_query_generator( $filtering_tax_query_generator );

		$sut->set_query( $query )
		    ->manage();

		Test::assertCount( 1, $sut->get_accessible_ids() );
		Test::assertCount( 1, $sut->get_accessible_ids( 'post__in' ) );
	}
}
