<?php

use tad\FunctionMocker\FunctionMocker as Test;

class trc_QueryRestrictorTest extends \PHPUnit_Framework_TestCase {

	protected function setUp() {
		Test::setUp();
	}

	protected function tearDown() {
		Test::tearDown();
	}

	/**
	 * @test
	 * it should not restrict the query if no_restriction is true
	 */
	public function it_should_not_restrict_the_query_if_no_restriction_is_true() {
		$sut = new trc_QueryRestrictor();

		Test::replace( 'apply_filters', function ( $tag, $value ) {
			return $value;
		} );

		$query = Test::replace( 'WP_Query' )->method( 'get', true )->get();

		Test::assertFalse( $sut->should_restrict_query( $query ) );
	}

	/**
	 * @test
	 * it should not restrict the query if restriction filter returns false
	 */
	public function it_should_not_restrict_the_query_if_restriction_filter_returns_falsy_value() {
		$sut = new trc_QueryRestrictor();

		Test::replace( 'apply_filters', false );
		$query = Test::replace( 'WP_Query' )->method( 'get', false )->get();

		Test::assertFalse( $sut->should_restrict_query( $query ) );
	}

	/**
	 * @test
	 * it should not restrict the query if the post type is not restricted
	 */
	public function it_should_not_restrict_the_query_if_the_post_type_is_not_restricted() {
		$sut = new trc_QueryRestrictor();

		Test::replace( 'apply_filters', function ( $tag, $value ) {
			$map = [
				'trc_should_be_restricted'    => true,
				'trc_is_restricted_post_type' => $value
			];

			return $map[ $tag ];
		} );
		$query      = Test::replace( 'WP_Query' )->method( 'get', function ( $key ) {
			$map = [
				'no_restriction' => false,
				'post_type'      => 'foo'
			];

			return $map[ $key ];
		} )->get();
		$post_types = Test::replace( 'trc_PostTypes' )->method( 'get_restricted_post_types', [ 'post' ] )->get();

		$sut->set_post_types( $post_types );

		Test::assertFalse( $sut->should_restrict_query( $query ) );
	}

	/**
	 * @test
	 * it should not restrict the query if no post type in the query is restricted
	 */
	public function it_should_not_restrict_the_query_if_no_post_type_in_the_query_is_restricted() {
		$sut = new trc_QueryRestrictor();

		Test::replace( 'apply_filters', function ( $tag, $value ) {
			$map = [
				'trc_should_be_restricted'    => true,
				'trc_is_restricted_post_type' => $value
			];

			return $map[ $tag ];
		} );
		$query      = Test::replace( 'WP_Query' )->method( 'get', function ( $key ) {
			$map = [
				'no_restriction' => false,
				'post_type'      => 'foo'
			];

			return $map[ $key ];
		} )->get();
		$post_types = Test::replace( 'trc_PostTypes' )->method( 'get_restricted_post_types', [ 'post', 'page' ] )
		                  ->get();

		$sut->set_post_types( $post_types );

		Test::assertFalse( $sut->should_restrict_query( $query ) );
	}

	/**
	 * @test
	 * it should not restrict the query if the restricted post types filter returns false
	 */
	public function it_should_not_restrict_the_query_if_the_restricted_post_types_filter_returns_false() {
		$sut = new trc_QueryRestrictor();

		Test::replace( 'apply_filters', function ( $tag, $value ) {
			$map = [
				'trc_should_be_restricted'    => true,
				'trc_is_restricted_post_type' => false
			];

			return $map[ $tag ];
		} );
		$query      = Test::replace( 'WP_Query' )->method( 'get', function ( $key ) {
			$map = [
				'no_restriction' => false,
				'post_type'      => 'post'
			];

			return $map[ $key ];
		} )->get();
		$post_types = Test::replace( 'trc_PostTypes' )->method( 'get_restricted_post_types', [ 'post', 'page' ] )
		                  ->get();

		$sut->set_post_types( $post_types );

		Test::assertFalse( $sut->should_restrict_query( $query ) );
	}

	/**
	 * @test
	 * it should not restrict the query if the current user can edit other user posts
	 */
	public function it_should_not_restrict_the_query_if_the_current_user_can_edit_other_user_posts() {
		$sut = new trc_QueryRestrictor();

		Test::replace( 'apply_filters', function ( $tag, $value ) {
			return $value;
		} );
		$query      = Test::replace( 'WP_Query' )->method( 'get', function ( $key ) {
			$map = [
				'no_restriction' => false,
				'post_type'      => 'post'
			];

			return $map[ $key ];
		} )->get();
		$post_types = Test::replace( 'trc_PostTypes' )->method( 'get_restricted_post_types', [ 'post', 'page' ] )
		                  ->get();

		$sut->set_post_types( $post_types );

		Test::replace( 'current_user_can', function ( $cap ) {
			return $cap == 'edit_others_posts';
		} );

		Test::assertFalse( $sut->should_restrict_query( $query ) );
	}

	/**
	 * @test
	 * it should not restrict the query if there are not restricting taxonomies
	 */
	public function it_should_not_restrict_the_query_if_there_are_not_restricting_taxonomies() {
		$sut = new trc_QueryRestrictor();

		Test::replace( 'apply_filters', function ( $tag, $value ) {
			return $value;
		} );
		$query      = Test::replace( 'WP_Query' )->method( 'get', function ( $key ) {
			$map = [
				'no_restriction' => false,
				'post_type'      => 'post'
			];

			return $map[ $key ];
		} )->get();
		$post_types = Test::replace( 'trc_PostTypes' )->method( 'get_restricted_post_types', [ 'post', 'page' ] )
		                  ->get();

		$sut->set_post_types( $post_types );

		$taxonomies = Test::replace( 'trc_Taxonomies' )->method( 'get_restricting_taxonomies', [ ] )->get();
		$sut->set_taxonomies( $taxonomies );

		Test::assertFalse( $sut->should_restrict_query( $query ) );
	}

	/**
	 * @test
	 * it should restrict the query if there is one restricting taxonomy
	 */
	public function it_should_restrict_the_query_if_there_is_one_restricting_taxonomy() {
		$sut = new trc_QueryRestrictor();

		Test::replace( 'apply_filters', function ( $tag, $value ) {
			return $value;
		} );
		$query      = Test::replace( 'WP_Query' )->method( 'get', function ( $key ) {
			$map = [
				'no_restriction' => false,
				'post_type'      => 'post'
			];

			return $map[ $key ];
		} )->get();
		$post_types = Test::replace( 'trc_PostTypes' )->method( 'get_restricted_post_types', [ 'post', 'page' ] )
		                  ->get();

		$sut->set_post_types( $post_types );

		$taxonomies = Test::replace( 'trc_Taxonomies' )->method( 'get_restricting_taxonomies', [ 'tax_a' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		Test::assertTrue( $sut->should_restrict_query( $query ) );
	}

	/**
	 * @test
	 * it should restrict the query if there are more restricting taxonomies
	 */
	public function it_should_restrict_the_query_if_there_are_more_restricting_taxonomies() {
		$sut = new trc_QueryRestrictor();

		Test::replace( 'apply_filters', function ( $tag, $value ) {
			return $value;
		} );
		$query      = Test::replace( 'WP_Query' )->method( 'get', function ( $key ) {
			$map = [
				'no_restriction' => false,
				'post_type'      => 'post'
			];

			return $map[ $key ];
		} )->get();
		$post_types = Test::replace( 'trc_PostTypes' )->method( 'get_restricted_post_types', [ 'post', 'page' ] )
		                  ->get();

		$sut->set_post_types( $post_types );

		$taxonomies = Test::replace( 'trc_Taxonomies' )->method( 'get_restricting_taxonomies', [ 'tax_a', 'tax_b' ] )
		                  ->get();
		$sut->set_taxonomies( $taxonomies );

		Test::assertTrue( $sut->should_restrict_query( $query ) );
	}

	/**
	 * @test
	 * it should restrict the query if at least one queried post type is restricted
	 */
	public function it_should_restrict_the_query_if_at_least_one_queried_post_type_is_restricted() {
		$sut = new trc_QueryRestrictor();

		Test::replace( 'apply_filters', function ( $tag, $value ) {
			return $value;
		} );
		$query      = Test::replace( 'WP_Query' )->method( 'get', function ( $key ) {
			$map = [
				'no_restriction' => false,
				'post_type'      => array( 'post', 'page', 'announcement' )
			];

			return $map[ $key ];
		} )->get();
		$post_types = Test::replace( 'trc_PostTypes' )->method( 'get_restricted_post_types', [ 'post' ] )->get();

		$sut->set_post_types( $post_types );

		$taxonomies = Test::replace( 'trc_Taxonomies' )->method( 'get_restricting_taxonomies', [ 'tax_a' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		Test::assertTrue( $sut->should_restrict_query( $query ) );
	}

	/**
	 * @test
	 * it should restrict the query if all the post types are restricted
	 */
	public function it_should_restrict_the_query_if_all_the_post_types_are_restricted() {
		$sut = new trc_QueryRestrictor();

		Test::replace( 'apply_filters', function ( $tag, $value ) {
			return $value;
		} );
		$query      = Test::replace( 'WP_Query' )->method( 'get', function ( $key ) {
			$map = [
				'no_restriction' => false,
				'post_type'      => [ 'post', 'page', 'announcement' ]
			];

			return $map[ $key ];
		} )->get();
		$post_types = Test::replace( 'trc_PostTypes' )->method( 'get_restricted_post_types', [
			'post',
			'page',
			'announcement'
		] )->get();

		$sut->set_post_types( $post_types );

		$taxonomies = Test::replace( 'trc_Taxonomies' )->method( 'get_restricting_taxonomies', [ 'tax_a' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		Test::assertTrue( $sut->should_restrict_query( $query ) );
	}

	/**
	 * @test
	 * it should restrict the query if no_restriction is not set
	 */
	public function it_should_restrict_the_query_if_no_restriction_is_not_set() {
		$sut = new trc_QueryRestrictor();

		Test::replace( 'apply_filters', function ( $tag, $value ) {
			return $value;
		} );
		$query      = Test::replace( 'WP_Query' )->method( 'get', function ( $key ) {
			$map = [
				'no_restriction' => null,
				'post_type'      => 'post'
			];

			return $map[ $key ];
		} )->get();
		$post_types = Test::replace( 'trc_PostTypes' )->method( 'get_restricted_post_types', [ 'post' ] )->get();

		$sut->set_post_types( $post_types );

		$taxonomies = Test::replace( 'trc_Taxonomies' )->method( 'get_restricting_taxonomies', [ 'tax_a' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		Test::assertTrue( $sut->should_restrict_query( $query ) );
	}

	/**
	 * @test
	 * it should restrict the query if no_restriction is set to false
	 */
	public function it_should_restrict_the_query_if_no_restriction_is_set_to_false() {
		$sut = new trc_QueryRestrictor();

		Test::replace( 'apply_filters', function ( $tag, $value ) {
			return $value;
		} );
		$query      = Test::replace( 'WP_Query' )->method( 'get', function ( $key ) {
			$map = [
				'no_restriction' => false,
				'post_type'      => 'post'
			];

			return $map[ $key ];
		} )->get();
		$post_types = Test::replace( 'trc_PostTypes' )->method( 'get_restricted_post_types', [ 'post' ] )->get();

		$sut->set_post_types( $post_types );

		$taxonomies = Test::replace( 'trc_Taxonomies' )->method( 'get_restricting_taxonomies', [ 'tax_a' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		Test::assertTrue( $sut->should_restrict_query( $query ) );
	}

	/**
	 * @test
	 * it should restrict the query if no_restriction value is overridden by filter
	 */
	public function it_should_restrict_the_query_if_no_restriction_value_is_overridden_by_filter() {
		$sut = new trc_QueryRestrictor();

		Test::replace( 'apply_filters', function ( $tag, $value ) {
			return $tag == 'trc_should_be_restricted' ? true : $value;
		} );
		$query      = Test::replace( 'WP_Query' )->method( 'get', function ( $key ) {
			$map = [
				'no_restriction' => true,
				'post_type'      => 'post'
			];

			return $map[ $key ];
		} )->get();
		$post_types = Test::replace( 'trc_PostTypes' )->method( 'get_restricted_post_types', [ 'post' ] )->get();

		$sut->set_post_types( $post_types );

		$taxonomies = Test::replace( 'trc_Taxonomies' )->method( 'get_restricting_taxonomies', [ 'tax_a' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		Test::assertTrue( $sut->should_restrict_query( $query ) );
	}

	/**
	 * @test
	 * it should restrict the query if restricted post types are overridden by filter
	 */
	public function it_should_restrict_the_query_if_restricted_post_types_are_overridden_by_filter() {
		$sut = new trc_QueryRestrictor();

		Test::replace( 'apply_filters', function ( $tag, $value ) {
			return $tag == 'trc_restricted_post_types' ? [ 'post' ] : $value;
		} );
		$query = Test::replace( 'WP_Query' )->method( 'get', function ( $key ) {
			$map = [
				'no_restriction' => false,
				'post_type'      => 'post'
			];

			return $map[ $key ];
		} )->get();
		$sut->set_post_types( trc_PostTypes::instance() );

		$taxonomies = Test::replace( 'trc_Taxonomies' )->method( 'get_restricting_taxonomies', [ 'tax_a' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		Test::assertTrue( $sut->should_restrict_query( $query ) );
	}

	/**
	 * @test
	 * it should restrict the query if taxonomies are overridden by filter
	 */
	public function it_should_restrict_the_query_if_taxonomies_are_overridden_by_filter() {
		$sut = new trc_QueryRestrictor();

		Test::replace( 'apply_filters', function ( $tag, $value ) {
			return $tag == 'trc_restricting_taxonomies' ? [ 'tax_a' ] : $value;
		} );
		$query = Test::replace( 'WP_Query' )->method( 'get', function ( $key ) {
			$map = [
				'no_restriction' => false,
				'post_type'      => 'post'
			];

			return $map[ $key ];
		} )->get();
		$sut->set_post_types( trc_PostTypes::instance() );
		$sut->set_taxonomies( trc_Taxonomies::instance() );

		Test::assertTrue( $sut->should_restrict_query( $query ) );
	}

	/**
	 * @test
	 * it should restrict the query if user can not edit other posts
	 */
	public function it_should_restrict_the_query_if_user_can_not_edit_other_posts() {
		$sut = new trc_QueryRestrictor();

		Test::replace( 'apply_filters', function ( $tag, $value ) {
			return $value;
		} );
		$query = Test::replace( 'WP_Query' )->method( 'get', function ( $key ) {
			$map = [
				'no_restriction' => false,
				'post_type'      => 'post'
			];

			return $map[ $key ];
		} )->get();

		$post_types = Test::replace( 'trc_PostTypes' )->method( 'get_restricted_post_types', [ 'post', 'page' ] )
		                  ->get();
		$sut->set_post_types( $post_types );

		$taxonomies = Test::replace( 'trc_Taxonomies' )->method( 'get_restricting_taxonomies', [ 'tax_a' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		Test::replace( 'current_user_can', false );

		Test::assertTrue( $sut->should_restrict_query( $query ) );
	}

	/**
	 * @test
	 * it should not add any restricting tax query if no restricting tax query is present
	 */
	public function it_should_not_add_any_restricting_tax_query_if_no_restricting_tax_query_is_present() {
		$sut = new trc_QueryRestrictor();

		$taxonomies = Test::replace( 'trc_Taxonomies' )->method( 'get_restricting_taxonomies', [ ] )->get();
		$sut->set_taxonomies( $taxonomies );

		$query                     = Test::replace( 'WP_Query' )->get();
		$query->tax_query          = new stdClass();
		$query->tax_query->queries = [ ];

		$sut->restrict_query( $query );

		Test::assertEmpty( $query->tax_query->queries );
	}

	/**
	 * @test
	 * it should add a restricting tax query if one restricting tax query is present
	 */
	public function it_should_add_a_restricting_tax_query_if_one_restricting_tax_query_is_present() {
		$sut = new trc_QueryRestrictor();

		$taxonomies = Test::replace( 'trc_Taxonomies' )->method( 'get_restricting_taxonomies', [ 'tax_a' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		$filtering_taxonomy = Test::replace( 'trc_FilteringTaxonomy' )->method( 'get_array_for', 'foo' )->get();
		$sut->set_filtering_taxonomy( $filtering_taxonomy );

		$query                     = Test::replace( 'WP_Query' )->get();
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
		$sut = new trc_QueryRestrictor();

		$taxonomies = Test::replace( 'trc_Taxonomies' )->method( 'get_restricting_taxonomies', [
			'tax_a',
			'tax_b',
			'tax_c'
		] )->get();
		$sut->set_taxonomies( $taxonomies );

		$filtering_taxonomy = Test::replace( 'trc_FilteringTaxonomy' )->method( 'get_array_for', 'foo' )->get();
		$sut->set_filtering_taxonomy( $filtering_taxonomy );

		$query                     = Test::replace( 'WP_Query' )->get();
		$query->tax_query          = new stdClass();
		$query->tax_query->queries = [ ];

		$sut->restrict_query( $query );

		Test::assertEquals( array( 'foo', 'foo', 'foo' ), $query->tax_query->queries );
	}

	/**
	 * @test
	 * it should leave previous tax queries in place when not adding restricting tax queries
	 */
	public function it_should_leave_previous_tax_queries_in_place_when_not_adding_restricting_tax_queries() {
		$sut = new trc_QueryRestrictor();

		$taxonomies = Test::replace( 'trc_Taxonomies' )->method( 'get_restricting_taxonomies', [ ] )->get();
		$sut->set_taxonomies( $taxonomies );

		$query                     = Test::replace( 'WP_Query' )->get();
		$query->tax_query          = new stdClass();
		$query->tax_query->queries = [ 'here before' ];

		$sut->restrict_query( $query );

		Test::assertEquals( array( 'here before' ), $query->tax_query->queries );
	}

	/**
	 * @test
	 * it should leave previous tax queries in place when adding restricting tax queries
	 */
	public function it_should_leave_previous_tax_queries_in_place_when_adding_restricting_tax_queries() {
		$sut = new trc_QueryRestrictor();

		$taxonomies = Test::replace( 'trc_Taxonomies' )->method( 'get_restricting_taxonomies', [ 'tax_a', 'tax_b' ] )
		                  ->get();
		$sut->set_taxonomies( $taxonomies );

		$filtering_taxonomy = Test::replace( 'trc_FilteringTaxonomy' )->method( 'get_array_for', 'foo' )->get();
		$sut->set_filtering_taxonomy( $filtering_taxonomy );

		$query                     = Test::replace( 'WP_Query' )->get();
		$query->tax_query          = new stdClass();
		$query->tax_query->queries = [ 'here before' ];

		$sut->restrict_query( $query );

		Test::assertEquals( array( 'here before', 'foo', 'foo' ), $query->tax_query->queries );
	}
}