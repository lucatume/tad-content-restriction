<?php

use tad\FunctionMocker\FunctionMocker as Test;

class trc_Core_QueryMarshalTest extends \PHPUnit_Framework_TestCase {

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
		Test::assertInstanceOf( 'trc_Core_QueryMarshal', new trc_Core_QueryMarshal() );
	}

	public function postTypesCombos() {
		return [
			[ [ 'post' ], [ 'post' ], [ 'tax_1' ], [ 'tax_1' ] ],
			[ [ 'post', 'page' ], [ 'post', 'page' ], [ 'tax_1' ], [ 'tax_1' ] ],
			[
				[ 'post', 'page' ],
				[ 'post', 'page' ],
				[ 'tax_1', 'tax_2' ],
				function ( $post_type ) {
					$map = [ 'post' => [ 'tax_1' ], 'page' => [ 'tax_2' ] ];

					return $map[ $post_type ];
				}
			],
			[
				[ 'post', 'page', 'notice' ],
				[ 'post', 'page' ],
				[ 'tax_1', 'tax_2', 'tax_3' ],
				function ( $post_type ) {
					$map = [ 'post' => [ 'tax_1', 'tax_2' ], 'page' => [ 'tax_3' ] ];

					return $map[ $post_type ];
				}
			],
			[
				[ 'post', 'page', 'notice' ],
				[ 'post', 'page' ],
				[ 'tax_1' ],
				[ 'tax_1' ]
			]
		];
	}

	/**
	 * @test
	 * it should make a query for each post type
	 * @dataProvider postTypesCombos
	 */
	public function it_should_make_a_query_for_each_post_type( $queried_post_types, $restricted_post_types, $restricting_taxonomies, $post_type_restricting_taxonomies ) {
		$sut = new trc_Core_QueryMarshal();

		$query = Test::replace( 'WP_Query' )
		             ->method( 'get', function ( $key, $default ) use ( $queried_post_types ) {
			             $map = [ 'post_type' => $queried_post_types ];

			             return isset( $map[ $key ] ) ? $map[ $key ] : $default;
		             } )
		             ->method( 'set' )
		             ->get();
		$sut->set_query( $query );

		$queried_restricted_post_types = array_intersect( $queried_post_types, $restricted_post_types );
		$scrutinizer                   = Test::replace( 'trc_Core_QueryScrutinizerInterface' )
		                                     ->method( 'get_queried_restricted_post_types', $queried_restricted_post_types )
		                                     ->get();
		$sut->set_query_scrutinizer( $scrutinizer );

		$taxonomies = Test::replace( 'trc_Core_RestrictingTaxonomiesInterface' )
		                  ->method( 'get_restricting_taxonomies_for', $post_type_restricting_taxonomies )
		                  ->get();
		$sut->set_restricting_taxonomies( $taxonomies );

		$tax_query_generator = Test::replace( 'trc_Core_FilteringTaxQueryGeneratorInterface' )
		                           ->method( 'get_tax_query_for', [ 'tax_query_here' ] )
		                           ->get();
		$sut->set_filtering_tax_query_generator( $tax_query_generator );

		$id_query = Test::replace( 'trc_Core_QueryInterface' )
		                ->method( 'set_args', '->' )
		                ->method( 'get_posts', [ 1, 2, 3 ] )
		                ->get();
		$sut->set_id_query( $id_query );

		$sut->set_excluded_posts();

		$query->wasCalledWithTimes( [ 'post__not_in' ], count( $queried_restricted_post_types ), 'set' );
	}

	public function filteringTaxQueryInputs() {
		return [
			[ [ 'not_relevant' ], [ ], [ 'tax_1' ] ],
			[ [ 'not_relevant' ], [ ], [ 'tax_1', 'tax_2' ] ],
			[ [ 'not_relevant' ], [ [ 'existing_tax_query_1' ] ], [ 'tax_1', 'tax_2' ] ],
			[ [ 'not_relevant' ], [ [ 'existing_tax_query_1' ], [ 'existing_tax_query_2' ] ], [ 'tax_1' ] ],
		];
	}

	/**
	 * @test
	 * it should set filtering tax query
	 * @dataProvider filteringTaxQueryInputs
	 */
	public function it_should_set_filtering_tax_query( $post_types, $existing_tax_queries, $restricting_taxonomies ) {
		$sut = new trc_Core_QueryMarshal();

		$query = Test::replace( 'WP_Query' )
		             ->method( 'set' )
		             ->get();
		if ( $existing_tax_queries ) {
			$query->tax_query          = new stdClass();
			$query->tax_query->queries = $existing_tax_queries;
		}
		$sut->set_query( $query );

		$scrutinizer = Test::replace( 'trc_Core_QueryScrutinizerInterface' )
		                   ->method( 'get_queried_restricted_post_types', $post_types )
		                   ->get();
		$sut->set_query_scrutinizer( $scrutinizer );

		$taxonomies = Test::replace( 'trc_Core_RestrictingTaxonomiesInterface' )
		                  ->method( 'get_restricting_taxonomies_for', $restricting_taxonomies )
		                  ->get();
		$sut->set_restricting_taxonomies( $taxonomies );

		$closure             = function ( $tax ) {
			return [ $tax . '_tax_query' ];
		};
		$tax_query_generator = Test::replace( 'trc_Core_FilteringTaxQueryGeneratorInterface' )
		                           ->method( 'get_tax_query_for', $closure )
		                           ->get();
		$sut->set_filtering_tax_query_generator( $tax_query_generator );

		$sut->set_filtering_tax_query();

		$new_tax_queries = [ ];
		foreach ( $restricting_taxonomies as $tax ) {
			$new_tax_queries[] = $closure( $tax );
		}
		$tax_queries = array_merge( $existing_tax_queries, $new_tax_queries );

		Test::assertCount( count( $new_tax_queries ) + count( $existing_tax_queries ), $query->tax_query->queries );
		Test::assertArraySubset( $existing_tax_queries, $query->tax_query->queries );
		$query->wasCalledWithOnce( [ 'tax_query', $tax_queries ], 'set' );
	}
}
