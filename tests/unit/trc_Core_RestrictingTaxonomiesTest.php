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

		global $wp_taxonomies;
		$wp_taxonomies = array( (object) [ 'name' => 'foo', 'object_type' => [ 'post' ] ] );

		Test::assertEquals( [ 'foo' ], $sut->get_restricting_taxonomies_for( 'post' ) );
	}

	/**
	 * @test
	 * it should return empty array if no taxonomies registered for post type
	 */
	public function it_should_return_empty_array_if_no_taxonomies_registered_for_post_type() {
		$sut = trc_Core_RestrictingTaxonomies::instance();

		global $wp_taxonomies;
		$wp_taxonomies = array();

		$taxonomies = $sut->get_restricting_taxonomies_for( [ 'post' ] );

		Test::assertEmpty( $taxonomies );
	}

	/**
	 * @test
	 * it should allow filtering the taxonomies adding them
	 */
	public function it_should_allow_filtering_the_taxonomies_adding_them() {
		$sut = trc_Core_RestrictingTaxonomies::instance();

		global $wp_taxonomies;
		$wp_taxonomies = array();

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

		global $wp_taxonomies;
		$wp_taxonomies = [
			(object) [ 'name' => 'foo', 'object_type' => [ 'post' ] ],
			(object) [ 'name' => 'baz', 'object_type' => [ 'post' ] ],
			(object) [ 'name' => 'bar', 'object_type' => [ 'post' ] ]
		];

		Test::replace( 'apply_filters', function ( $tag, $val ) {

			return $tag == 'trc_post_type_restricting_taxonomies' ? [ 'tax_a' ] : $val;
		} );

		Test::assertEquals( [ 'tax_a' ], $sut->get_restricting_taxonomies_for( 'post' ) );
	}
}
