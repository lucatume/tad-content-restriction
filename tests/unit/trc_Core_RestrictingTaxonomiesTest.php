<?php

use tad\FunctionMocker\FunctionMocker as Test;

class trc_Core_RestrictingTaxonomiesTest extends \PHPUnit_Framework_TestCase {

	protected function setUp() {
		Test::setUp();

		// filter mock
		Test::replace( 'apply_filters', function ( $tag, $val ) {
			return $val;
		} );
	}

	protected function tearDown() {
		Test::tearDown();
	}

	/**
	 * @test
	 * it should be instatiatable
	 */
	public function it_should_be_instatiatable() {
		Test::assertInstanceOf( 'trc_Core_RestrictingTaxonomies', new trc_Core_RestrictingTaxonomies() );
	}

	/**
	 * @test
	 * it should allow adding taxonomies
	 */
	public function it_should_allow_adding_taxonomies() {
		$sut = trc_Core_RestrictingTaxonomies::instance();

		$sut->add( 'foo' );
		Test::replace( 'get_taxonomies', [ 'foo' => 23 ] );

		Test::assertEquals( [ 'foo' ], $sut->get_restricting_taxonomies_for( 'post' ) );
	}

	/**
	 * @test
	 * it should query the taxonomies for the object types
	 */
	public function it_should_query_the_taxonomies_for_the_object_types() {
		$sut = trc_Core_RestrictingTaxonomies::instance();

		$get_taxonomies = Test::replace( 'get_taxonomies', [ 'foo' => 23 ] );

		$sut->get_restricting_taxonomies_for( 'post' );

		$get_taxonomies->wasCalledWithOnce( [ [ 'object_type' => [ 'post' ] ] ] );
	}

	/**
	 * @test
	 * it should query the taxonomies for multiple object types
	 */
	public function it_should_query_the_taxonomies_for_multiple_object_types() {
		$sut = trc_Core_RestrictingTaxonomies::instance();

		$get_taxonomies = Test::replace( 'get_taxonomies', [ 'foo' => 23 ] );

		$sut->get_restricting_taxonomies_for( [ 'post', 'page' ] );

		$get_taxonomies->wasCalledWithOnce( [ [ 'object_type' => [ 'post', 'page' ] ] ] );
	}

	/**
	 * @test
	 * it should return empty array if no taxonomies registered for post type
	 */
	public function it_should_return_empty_array_if_no_taxonomies_registered_for_post_type() {
		$sut = trc_Core_RestrictingTaxonomies::instance();

		Test::replace( 'get_taxonomies', [ ] );

		$taxonomies = $sut->get_restricting_taxonomies_for( [ 'post' => 23 ] );

		Test::assertEmpty( $taxonomies );
	}

	/**
	 * @test
	 * it should allow filtering the taxonomies adding them
	 */
	public function it_should_allow_filtering_the_taxonomies_adding_them() {
		$sut = trc_Core_RestrictingTaxonomies::instance();

		Test::replace( 'get_taxonomies', [ 'tax_a' => 23, 'tax_b' => 23 ] );

		Test::replace( 'apply_filters', function ( $tag, $val ) {

			return $tag == 'trc_post_type_restricting_taxonomies' ? [ 'tax_c', 'tax_d' ] : $val;
		} );

		Test::assertEquals( [ 'tax_c', 'tax_d' ], $sut->get_restricting_taxonomies_for( 'post' ) );
	}

	/**
	 * @test
	 * it should allow filtering the taxonomies removing them
	 */
	public function it_should_allow_filtering_the_taxonomies_removing_them() {
		$sut = trc_Core_RestrictingTaxonomies::instance();

		Test::replace( 'get_taxonomies', [ 'tax_a' => 23, 'tax_b' => 23, 'tax_c' => 23 ] );

		Test::replace( 'apply_filters', function ( $tag, $val ) {

			return $tag == 'trc_post_type_restricting_taxonomies' ? [ 'tax_a' ] : $val;
		} );

		Test::assertEquals( [ 'tax_a' ], $sut->get_restricting_taxonomies_for( 'post' ) );
	}
}
