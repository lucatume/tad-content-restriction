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
		Test::assertInstanceOf( 'trc_Core_QueryScrutinizer', new trc_Core_QueryScrutinizer() );
	}

	public function mixedPostTypes() {
		return [
			// expected, post types, restricted post types
			[ true, [ 'post', 'page' ], [ 'post' ] ],
			[ false, [ 'post', 'page' ], [ 'post', 'page' ] ],
			[ false, [ 'post', 'page' ], [ ] ],
			[ true, [ 'post', 'page', 'notice' ], [ 'post', 'page' ] ],
			[ false, [ 'post', 'page', 'notice' ], [ ] ],
			[ false, [ 'post' ], [ 'page', 'notice' ] ],
			[ false, [ ], [ 'post', 'page' ] ],
			[ false, [ ], [ 'post' ] ]
		];
	}

	/**
	 * @test
	 * it should spot mixed restriction post type queries
	 * @dataProvider mixedPostTypes
	 */
	public function it_should_spot_mixed_restriction_post_type_queries( $expected, $post_types, $restricted_post_types ) {

		$query      = Test::replace( 'WP_Query' )
		                  ->method( 'get', function ( $key, $default ) use ( $post_types ) {
			                  return $key == 'post_type' ? $post_types : $default;
		                  } )
		                  ->get();
		$post_types = Test::replace( 'trc_Core_PostTypesInterface' )
		                  ->method( 'get_restricted_post_types_in', $restricted_post_types )
		                  ->get();

		$sut = new trc_Core_QueryScrutinizer();
		$sut->set_post_types( $post_types );

		$sut->set_query( $query )
		    ->scrutinize();

		Test::assertEquals( $expected, $sut->is_mixed_restriction_query() );
	}

	public function restrictedPostTypes() {
		return [
			// expected, post types, restricted post types
			[ true, [ 'post', 'page' ], [ 'post' ] ],
			[ true, [ 'post', 'page' ], [ 'post', 'page' ] ],
			[ false, [ 'post', 'page' ], [ ] ],
			[ true, [ 'post', 'page', 'notice' ], [ 'post', 'page' ] ],
			[ false, [ 'post', 'page', 'notice' ], [ ] ],
			[ false, [ 'post' ], [ ] ],
			[ false, [ ], [ ] ],
			[ false, [ ], [ ] ]
		];
	}

	/**
	 * @test
	 * it should properly identify queries for restricted post types
	 * @dataProvider restrictedPostTypes
	 */
	public function it_should_properly_identify_queries_for_restricted_post_types( $expected, $post_types, $restricted_post_types ) {

		$query      = Test::replace( 'WP_Query' )
		                  ->method( 'get', function ( $key, $default ) use ( $post_types ) {
			                  return $key == 'post_type' ? $post_types : $default;
		                  } )
		                  ->get();
		$post_types = Test::replace( 'trc_Core_PostTypesInterface' )
		                  ->method( 'get_restricted_post_types_in', $restricted_post_types )
		                  ->get();

		$sut = new trc_Core_QueryScrutinizer();
		$sut->set_post_types( $post_types );

		$sut->set_query( $query )
		    ->scrutinize();

		Test::assertEquals( $expected, $sut->is_querying_restricted_post_types() );
	}

	/**
	 * @test
	 * it should set the excluded posts on the query
	 */
	public function it_should_set_the_excluded_posts_on_the_query() {
		$taxonomies            = [ 'tax_1' ];
		$excluded_post_ids     = [ 1, 2, 3 ];
		$restricted_post_types = [ 'post' ];
		$queried_post_types    = [ 'post', 'page' ];

		$restricting_taxonomies = Test::replace( 'trc_Core_RestrictingTaxonomiesInterface' )
		                              ->method( 'get_restricting_taxonomies', $taxonomies )
		                              ->method( 'get_restricting_taxonomies_for', $taxonomies )
		                              ->get( '' );
		$post_types             = Test::replace( 'trc_Core_RestrictedPostTypesInterface' )
		                              ->method( 'get_restricted_post_types_in', $restricted_post_types )
		                              ->get();
		$excluded_posts_query   = Test::replace( 'trc_Core_ExcludedPostsQueryInterface' )
		                              ->method( 'get_excluded_posts', $excluded_post_ids )
		                              ->get();
		$query                  = Test::replace( 'WP_Query' )
		                              ->method( 'set' )
		                              ->method( 'get', function ( $key, $default ) use ( $queried_post_types ) {
			                              return $key == 'post_type' ? $queried_post_types : $default;
		                              } )
		                              ->get();

		$sut = new trc_Core_QueryScrutinizer();
		$sut->set_restricting_taxonomies( $restricting_taxonomies );
		$sut->set_excluded_posts_query( $excluded_posts_query );
		$sut->set_post_types( $post_types );
		$sut->set_query( $query );

		$sut->set_excluded_posts();

		$query->wasCalledWithOnce( [ 'post__not_in', $excluded_post_ids ], 'set' );
	}
}
