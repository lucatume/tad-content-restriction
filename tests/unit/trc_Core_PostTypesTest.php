<?php

use tad\FunctionMocker\FunctionMocker as Test;

class trc_Core_PostTypesTest extends \PHPUnit_Framework_TestCase {

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
	 * it should allow filtering the restricted post types
	 */
	public function it_should_allow_filtering_the_restricted_post_types() {
		$sut = new trc_Core_PostTypes();

		Test::replace( 'apply_filters', function ( $tag, $val ) {
			return $tag == 'trc_restricted_post_types' ? [ 'foo', 'bar' ] : $val;
		} );

		Test::assertEquals( [ 'foo', 'bar' ], $sut->get_restricted_post_types() );
	}

	/**
	 * @test
	 * it should restrict the post type by default
	 */
	public function it_should_not_restrict_any_post_type_by_default() {
		$sut = new trc_Core_PostTypes();

		Test::replace( 'apply_filters', function ( $tag, $val ) {
			return $val;
		} );

		Test::assertEquals( [ 'post' ], $sut->get_restricted_post_types() );
	}

	/**
	 * @test
	 * it should allow filtering if a post type is restricted
	 */
	public function it_should_allow_filtering_if_a_post_type_is_restricted() {
		$sut = new trc_Core_PostTypes();

		Test::replace( 'apply_filters', function ( $tag, $val ) {
			$map = [
				'trc_restricted_post_types'   => [ 'foo', 'bar' ],
				'trc_is_restricted_post_type' => true
			];

			return isset( $map[ $tag ] ) ? $map[ $tag ] : $val;
		} );

		Test::assertTrue( $sut->is_restricted_post_type( 'wot' ) );
	}

	/**
	 * @test
	 * it should allow adding a restricted post type
	 */
	public function it_should_allow_adding_a_restricted_post_type() {
		$sut = new trc_Core_PostTypes();

		$sut->add_restricted_post_type( 'post' );

		Test::assertEquals( [ 'post' ], $sut->get_restricted_post_types() );
	}

	/**
	 * @test
	 * it should allow adding an array of post types
	 */
	public function it_should_allow_adding_an_array_of_post_types() {
		$sut = new trc_Core_PostTypes();

		$sut->add_restricted_post_type( [ 'page', 'notice' ] );

		Test::assertEquals( [ 'post', 'page', 'notice' ], $sut->get_restricted_post_types() );
	}

	/**
	 * @test
	 * it should allow removing restricted post types
	 */
	public function it_should_allow_removing_restricted_post_types() {
		$sut = new trc_Core_PostTypes();

		$sut->add_restricted_post_type( 'post' );
		$sut->add_restricted_post_type( 'page' );

		Test::assertEquals( [ 'post', 'page' ], $sut->get_restricted_post_types() );

		$sut->remove_restricted_post_type( 'post' );

		Test::assertEquals( [ 'page' ], $sut->get_restricted_post_types() );
	}

	/**
	 * @test
	 * it should mark group of post types as restricted if one is restricted
	 */
	public function it_should_mark_group_of_post_types_as_restricted_if_one_is_restricted() {
		$sut = new trc_Core_PostTypes();

		$sut->add_restricted_post_type( 'post' );

		Test::assertTrue( $sut->is_restricted_post_type( [ 'post', 'page' ] ) );
	}

	public function postTypes() {
		return [
			[ [ 'post', 'page' ], [ 'post' ] ],
			[ [ 'post' ], [ 'post' ] ],
			[ [ 'page' ], [ ] ],
			[ 'post', [ 'post' ] ],
			[ 'page', [ ] ],
			[ [ 'page', 'post', 'notice' ], [ 'post', 'notice' ] ],
			[ [ 'post', 'notice' ], [ 'post', 'notice' ] ],
		];
	}

	/**
	 * @test
	 * it should allow getting restricted post types in a post type array
	 * @dataProvider postTypes
	 */
	public function it_should_allow_getting_restricted_post_types_in_a_post_type_array( $in, $out ) {
		$sut = new trc_Core_PostTypes();

		$sut->add_restricted_post_type( [ 'post', 'notice' ] );

		Test::assertEquals( $out, $sut->get_restricted_post_types_in( $in ) );
	}
}
