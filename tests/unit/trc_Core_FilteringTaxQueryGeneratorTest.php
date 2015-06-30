<?php

use tad\FunctionMocker\FunctionMocker as Test;

class trc_Core_FilteringTaxQueryGeneratorTest extends \PHPUnit_Framework_TestCase {

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
		Test::assertInstanceOf( 'trc_Core_FilteringTaxQueryGenerator', new trc_Core_FilteringTaxQueryGenerator() );
	}

	/**
	 * @test
	 * it should return WP_Error if user is not set
	 */
	public function it_should_return_wp_error_if_user_is_not_set() {
		$sut = new trc_Core_FilteringTaxQueryGenerator();

		Test::assertInstanceOf( 'WP_Error', $sut->get_tax_query_for( 'foo' ) );
	}

	/**
	 * @test
	 * it should return WP_Error if taxonomy is not a string
	 */
	public function it_should_return_wp_error_if_taxonomy_is_not_a_string() {
		$sut = new trc_Core_FilteringTaxQueryGenerator();

		Test::assertInstanceOf( 'WP_Error', $sut->get_tax_query_for( 23 ) );
	}

	/**
	 * @test
	 * it should return empty array for empty user slugs
	 */
	public function it_should_return_empty_array_for_empty_user_slugs() {
		$sut = new trc_Core_FilteringTaxQueryGenerator();

		$exp  = [ 'taxonomy' => 'foo', 'field' => 'slug', 'terms' => [ ], 'operator' => 'IN' ];
		$user = Test::replace( 'trc_Core_UserInterface' )->method( 'get_user_slugs_for', [ ] )->get();

		$sut->set_user( $user );

		Test::assertEquals( $exp, $sut->get_tax_query_for( 'foo' ) );
	}

	/**
	 * @test
	 * it should return proper tax query for user terms
	 */
	public function it_should_return_proper_tax_query_for_user_terms() {
		$sut = new trc_Core_FilteringTaxQueryGenerator();

		$user_terms = [ 'term_1', 'term_2' ];
		$exp        = [ 'taxonomy' => 'foo', 'field' => 'slug', 'terms' => $user_terms, 'operator' => 'IN' ];
		$user       = Test::replace( 'trc_Core_UserInterface' )->method( 'get_user_slugs_for', $user_terms )->get();

		$sut->set_user( $user );

		Test::assertEquals( $exp, $sut->get_tax_query_for( 'foo' ) );
	}
}
