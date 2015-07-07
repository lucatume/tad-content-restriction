<?php

use tad\FunctionMocker\FunctionMocker as Test;

class trc_Core_QueryScrutinizerTest extends \PHPUnit_Framework_TestCase {

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
	public function restricted_and_unrestricted_post_types( $expected, $post_types, $restricted_post_types ) {

		$query      = Test::replace( 'WP_Query' )
		                  ->method( 'get', function ( $key, $default ) use ( $post_types ) {
			                  return $key == 'post_type' ? $post_types : $default;
		                  } )
		                  ->get();
		$post_types = Test::replace( 'trc_Core_PostTypesInterface' )
		                  ->method( 'get_restricted_post_types_in', array_intersect( $post_types, $restricted_post_types ) )
		                  ->get();
		$taxonomies = Test::replace( 'trc_Core_RestrictingTaxonomiesInterface' )
		                  ->method( 'get_restricting_taxonomies', [ 'tax_1' ] )
		                  ->method( 'get_restricting_taxonomies_for', [ 'tax_1' ] )
		                  ->get();

		$sut = new trc_Core_QueryScrutinizer();
		$sut->set_post_types( $post_types );
		$sut->set_restricting_taxonomies( $taxonomies );

		$sut->set_query( $query )
		    ->scrutinize();

		Test::assertEquals( $expected, $sut->is_mixed_restriction_query() );
	}

	public function restrictingTaxonomiesAndPostTypesCombos() {
		return [
			[
				true,
				[ 'post', 'page' ],
				[ 'tax_1', 'tax_2' ],
				function ( $post_type ) {
					$map = [ 'post' => [ 'tax_1' ], 'page' => [ 'tax_2' ] ];

					return $map[ $post_type ];
				}
			],
			[
				false,
				[ 'post', 'page' ],
				[ 'tax_1' ],
				[ 'tax_1' ]
			],
			[
				true,
				[ 'post', 'page' ],
				[ 'tax_1', 'tax_2', 'tax_3' ],
				function ( $post_type ) {
					$map = [ 'post' => [ 'tax_1', 'tax_2' ], 'page' => [ 'tax_3' ] ];

					return $map[ $post_type ];
				}
			],
			[
				false,
				[ 'post', 'page' ],
				[ 'tax_1', 'tax_2' ],
				[ 'tax_1', 'tax_2' ]
			],
			[
				true,
				[ 'post', 'page' ],
				[ 'tax_1', 'tax_2', 'tax_3', 'tax_4' ],
				function ( $post_type ) {
					$map = [ 'post' => [ 'tax_1', 'tax_2' ], 'page' => [ 'tax_3', 'tax_4' ] ];

					return $map[ $post_type ];
				}
			],
			[
				true,
				[ 'post', 'page' ],
				[ 'tax_1', 'tax_2' ],
				function ( $post_type ) {
					$map = [ 'post' => [ 'tax_1', 'tax_2' ], 'page' => [ 'tax_1' ] ];

					return $map[ $post_type ];
				}
			],
			[
				true,
				[ 'post', 'page' ],
				[ 'tax_1', 'tax_2', 'tax_3' ],
				function ( $post_type ) {
					$map = [ 'post' => [ 'tax_1', 'tax_2', 'tax_3' ], 'page' => [ 'tax_1', 'tax_2' ] ];

					return $map[ $post_type ];
				}
			]
		];
	}

	/**
	 * @test
	 * it should spot mixed restriction queries in respect to restricting taxonomies
	 * @dataProvider restrictingTaxonomiesAndPostTypesCombos
	 */
	public function different_restricting_taxonomies( $expected, $post_types, $restricting_taxonomies, $post_type_restricting_taxonomies ) {
		$query      = Test::replace( 'WP_Query' )
		                  ->method( 'get', function ( $key, $default ) use ( $post_types ) {
			                  return $key == 'post_type' ? $post_types : $default;
		                  } )
		                  ->get();
		$post_types = Test::replace( 'trc_Core_PostTypesInterface' )
		                  ->method( 'ger_restricted_post_types', $post_types )
		                  ->method( 'get_restricted_post_types_in', $post_types )
		                  ->get();
		$taxonomies = Test::replace( 'trc_Core_RestrictingTaxonomiesInterface' )
		                  ->method( 'get_restricting_taxonomies', $restricting_taxonomies )
		                  ->method( 'get_restricting_taxonomies_for', $post_type_restricting_taxonomies )
		                  ->get();

		$sut = new trc_Core_QueryScrutinizer();
		$sut->set_post_types( $post_types );
		$sut->set_restricting_taxonomies( $taxonomies );

		$sut->set_query( $query )
		    ->scrutinize();

		Test::assertEquals( $expected, $sut->is_mixed_restriction_query() );
	}

	public function restrictedPostTypes() {
		return [
			// expected, post types, restricted post types
			[ true, [ 'post', 'page' ], [ 'post' ], [ 'tax_1' ], [ 'tax_1' ] ],
			[ true, [ 'post', 'page' ], [ 'post', 'page' ], [ 'tax_1' ], [ 'tax_1' ] ],
			[ false, [ 'post', 'page' ], [ ], [ 'tax_1' ], [ 'tax_1' ] ],
			[ true, [ 'post', 'page', 'notice' ], [ 'post', 'page' ], [ 'tax_1' ], [ 'tax_1' ] ],
			[ false, [ 'post', 'page', 'notice' ], [ ], [ 'tax_1' ], [ 'tax_1' ] ],
			[ false, [ 'post' ], [ ], [ 'tax_1' ], [ 'tax_1' ] ],
			[ false, [ ], [ ], [ 'tax_1' ], [ 'tax_1' ] ],
			[ false, [ 'post' ], [ 'post' ], [ 'tax_1' ], [ ] ],
			[ false, [ 'post', 'page' ], [ 'post', 'page' ], [ 'tax_1' ], [ ] ],
			[ false, [ 'post', 'page' ], [ 'post', 'page' ], [ 'tax_1', 'tax_2' ], [ ] ]
		];
	}

	/**
	 * @test
	 * it should properly identify queries for restricted post types
	 * @dataProvider restrictedPostTypes
	 */
	public function it_should_properly_identify_queries_for_restricted_post_types( $expected, $post_types, $restricted_post_types, $restricting_taxonomies, $post_type_restricting_taxonomies ) {

		$query      = Test::replace( 'WP_Query' )
		                  ->method( 'get', function ( $key, $default ) use ( $post_types ) {
			                  return $key == 'post_type' ? $post_types : $default;
		                  } )
		                  ->get();
		$post_types = Test::replace( 'trc_Core_PostTypesInterface' )
		                  ->method( 'get_restricted_post_types_in', $restricted_post_types )
		                  ->get();
		$taxonomies = Test::replace( 'trc_Core_RestrictingTaxonomiesInterface' )
		                  ->method( 'get_restricting_taxonomies', $restricting_taxonomies )
		                  ->method( 'get_restricting_taxonomies_for', $post_type_restricting_taxonomies )
		                  ->get();

		$sut = new trc_Core_QueryScrutinizer();
		$sut->set_post_types( $post_types );
		$sut->set_restricting_taxonomies( $taxonomies );

		$sut->set_query( $query )
		    ->scrutinize();

		Test::assertEquals( $expected, $sut->is_querying_restricted_post_types() );
	}
}
