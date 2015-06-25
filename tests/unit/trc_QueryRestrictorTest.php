<?php

use tad\FunctionMocker\FunctionMocker as Test;

class trc_QueryRestrictorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		Test::assertInstanceOf( 'trc_QueryRestrictor', new trc_QueryRestrictor() );
	}

	/**
	 * @test
	 * it should not restrict the query if there are no restricting taxonomies
	 */
	public function it_should_not_restrict_the_query_if_there_are_no_restricting_taxonomies() {
		$sut = new trc_QueryRestrictor();

		$taxonomies = Test::replace( 'trc_taxonomies' )->method( 'get_restricting_taxonomies', [ ] )->get();
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
	public function it_should_not_restrict_the_query_if_no_restriction_is_true() {
		$sut = new trc_QueryRestrictor();

		$taxonomies = Test::replace( 'trc_taxonomies' )->method( 'get_restricting_taxonomies', [ 'tax_a' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		$queries = Test::replace( 'trc_Queries' )->method( 'should_restrict_queries', false )->get();
		$sut->set_queries( $queries );

		Test::assertFalse( $sut->should_restrict_query( $this->get_mock_query() ) );
	}

	/**
	 * @test
	 * it should not restrict the query if the query is not to be restricted
	 */
	public function it_should_not_restrict_the_query_if_the_query_is_not_to_be_restricted() {
		$sut = new trc_QueryRestrictor();

		$taxonomies = Test::replace( 'trc_taxonomies' )->method( 'get_restricting_taxonomies', [ 'tax_a' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		$queries = Test::replace( 'trc_Queries' )->method( 'should_restrict_queries', true )
		               ->method( 'should_restrict_query', false )->get();
		$sut->set_queries( $queries );

		Test::assertFalse( $sut->should_restrict_query( $this->get_mock_query() ) );
	}

	/**
	 * @test
	 * it should not restrict the query if the post type is not a restricted post type
	 */
	public function it_should_not_restrict_the_query_if_the_post_type_is_not_a_restricted_post_type() {
		$sut = new trc_QueryRestrictor();

		$taxonomies = Test::replace( 'trc_taxonomies' )->method( 'get_restricting_taxonomies', [ 'tax_a' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		$queries = Test::replace( 'trc_Queries' )->method( 'should_restrict_queries', true )
		               ->method( 'should_restrict_query', true )->get();
		$sut->set_queries( $queries );

		$post_types = Test::replace( 'trc_PostTypes' )->method( 'is_restricted_post_type', false )->get();
		$sut->set_post_types( $post_types );

		Test::assertFalse( $sut->should_restrict_query( $this->get_mock_query() ) );
	}

	/**
	 * @test
	 * it should not restrict the query if the user can access it
	 */
	public function it_should_not_restrict_the_query_if_the_user_can_access_it() {
		$sut = new trc_QueryRestrictor();

		$taxonomies = Test::replace( 'trc_taxonomies' )->method( 'get_restricting_taxonomies', [ 'tax_a' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		$queries = Test::replace( 'trc_Queries' )->method( 'should_restrict_queries', true )
		               ->method( 'should_restrict_query', true )->get();
		$sut->set_queries( $queries );

		$post_types = Test::replace( 'trc_PostTypes' )->method( 'is_restricted_post_type', true )->get();
		$sut->set_post_types( $post_types );

		$user = Test::replace( 'trc_User' )->method( 'can_access_query', true )->get();
		$sut->set_user( $user );

		Test::assertFalse( $sut->should_restrict_query( $this->get_mock_query() ) );
	}

	protected function setUp() {
		Test::setUp();
	}

	protected function tearDown() {
		Test::tearDown();
	}

}