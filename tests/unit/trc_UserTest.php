<?php

use tad\FunctionMocker\FunctionMocker as Test;

class trc_UserTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		Test::assertInstanceOf( 'trc_User', new trc_User() );
	}

	/**
	 * @test
	 * it should return WP_Error if the post is not a valid post
	 */
	public function it_should_return_WP_Error_if_the_post_is_not_a_valid_post() {
		Test::replace( 'get_post', null );

		$sut = new trc_User();

		Test::assertInstanceOf( 'WP_Error', $sut->can_access_post() );
	}

	/**
	 * @test
	 * it should allow setting user slugs provider
	 */
	public function it_should_allow_setting_user_slugs_provider() {
		$sut = new trc_User();

		$provider_1 = Test::replace( 'trc_UserSlugProviderInterface' )->get();
		$provider_2 = Test::replace( 'trc_UserSlugProviderInterface' )->get();

		$sut->add_user_slug_provider( 'foo', $provider_1 );
		$sut->add_user_slug_provider( 'bar', $provider_2 );

		Test::assertEquals( [ 'foo' => $provider_1, 'bar' => $provider_2 ], $sut->get_user_slug_providers() );
	}

	/**
	 * @test
	 * it should allow removing a user slugs provider
	 */
	public function it_should_allow_removing_a_user_slugs_provider() {
		$sut = new trc_User();

		$provider_1 = Test::replace( 'trc_UserSlugProviderInterface' )->get();
		$provider_2 = Test::replace( 'trc_UserSlugProviderInterface' )->get();

		$sut->add_user_slug_provider( 'foo', $provider_1 );
		$sut->add_user_slug_provider( 'bar', $provider_2 );
		$sut->remove_user_slug_provider( 'foo' );

		Test::assertEquals( [ 'bar' => $provider_2 ], $sut->get_user_slug_providers() );
	}

	/**
	 * @test
	 * it should allow user to access post if there are no restricting taxonomies defined
	 */
	public function it_should_allow_user_to_access_post_if_there_are_no_restricting_taxonomies_defined() {
		$sut             = new trc_User();
		$post            = new stdClass();
		$post->post_type = 'post';
		Test::replace( 'get_post', $post );
		$taxonomies = Test::replace( 'trc_RestrictingTaxonomiesInterface' )->method( 'get_restricting_taxonomies', [ ] )
		                  ->get();

		$sut->set_taxonomies( $taxonomies );

		Test::assertTrue( $sut->can_access_post() );
	}

	/**
	 * @test
	 * it should allow the user to access a post that has no terms assigned for a restriction taxonomy
	 */
	public function it_should_allow_the_user_to_access_a_post_that_has_no_terms_assigned_for_a_restriction_taxonomy() {
		$sut = new trc_User();

		$post            = new stdClass();
		$post->ID        = 23;
		$post->post_type = 'post';
		Test::replace( 'get_post', $post );

		$taxonomies = Test::replace( 'trc_RestrictingTaxonomiesInterface' )
		                  ->method( 'get_restricting_taxonomies', [ 'tax_a' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		Test::replace( 'wp_get_object_terms', array() );

		$provider = Test::replace( 'trc_UserSlugProviderInterface' )->method( 'get_user_slugs_for', [
			'term_1',
			'term_2'
		] )->get();
		$sut->add_user_slug_provider( 'tax_a', $provider );

		Test::assertTrue( $sut->can_access_post() );
	}

	/**
	 * @test
	 * it should allow the user to access a post that has not one term assigned for each restricting taxonomy
	 */
	public function it_should_allow_the_user_to_access_a_post_that_has_not_one_term_assigned_for_each_restricting_taxonomy() {
		$sut = new trc_User();

		$post            = new stdClass();
		$post->ID        = 23;
		$post->post_type = 'post';
		Test::replace( 'get_post', $post );

		$taxonomies = Test::replace( 'trc_RestrictingTaxonomiesInterface' )
		                  ->method( 'get_restricting_taxonomies', [ 'tax_a', 'tax_b' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		Test::replace( 'wp_get_object_terms', function ( $_, $tax ) {
			$map = [
				'tax_a' => [ 'term_1', 'term_2' ],
				'tax_b' => [ ]
			];

			return $map[ $tax ];
		} );

		$provider = Test::replace( 'trc_UserSlugProviderInterface' )->method( 'get_user_slugs', [
			'term_1',
			'term_2'
		] )->get();
		$sut->add_user_slug_provider( 'tax_a', $provider );
		$sut->add_user_slug_provider( 'tax_b', $provider );

		Test::assertTrue( $sut->can_access_post() );
	}

	/**
	 * @test
	 * it should allow the user to access the post if there are no user slug providers
	 */
	public function it_should_allow_the_user_to_access_the_post_if_there_are_no_user_slug_providers() {
		$sut = new trc_User();

		$post            = new stdClass();
		$post->ID        = 23;
		$post->post_type = 'post';
		Test::replace( 'get_post', $post );

		$taxonomies = Test::replace( 'trc_RestrictingTaxonomiesInterface' )
		                  ->method( 'get_restricting_taxonomies', [ 'tax_a', 'tax_b' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		Test::replace( 'wp_get_object_terms', function ( $_, $tax ) {
			$map = [
				'tax_a' => [ 'term_1', 'term_2' ],
				'tax_b' => [ 'term_3' ]
			];

			return $map[ $tax ];
		} );

		Test::assertTrue( $sut->can_access_post() );
	}

	/**
	 * @test
	 * it should not allow the user to access the post if the slug provider returns an empty array for the taxonomy
	 */
	public function it_should_not_allow_the_user_to_access_the_post_if_the_slug_provider_returns_an_empty_array_for_the_taxonomy() {
		$sut = new trc_User();

		$post            = new stdClass();
		$post->ID        = 23;
		$post->post_type = 'post';
		Test::replace( 'get_post', $post );

		$taxonomies = Test::replace( 'trc_RestrictingTaxonomiesInterface' )
		                  ->method( 'get_restricting_taxonomies', [ 'tax_a' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		Test::replace( 'wp_get_object_terms', function ( $_, $tax ) {
			$map = [
				'tax_a' => [ 'term_1', 'term_2' ]
			];

			return $map[ $tax ];
		} );

		$provider = Test::replace( 'trc_UserSlugProviderInterface' )->method( 'get_user_slugs_for', [ ] )->get();
		$sut->add_user_slug_provider( 'tax_a', $provider );

		Test::assertFalse( $sut->can_access_post() );
	}

	/**
	 * @test
	 * it should allow the user to access the post if the user has at least one access term
	 */
	public function it_should_allow_the_user_to_access_the_post_if_the_user_has_at_least_one_access_term() {
		$sut = new trc_User();

		$post            = new stdClass();
		$post->ID        = 23;
		$post->post_type = 'post';
		Test::replace( 'get_post', $post );

		$taxonomies = Test::replace( 'trc_RestrictingTaxonomiesInterface' )
		                  ->method( 'get_restricting_taxonomies', [ 'tax_a' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		Test::replace( 'wp_get_object_terms', [ 'term_1', 'term_2' ] );

		$provider = Test::replace( 'trc_UserSlugProviderInterface' )->method( 'get_user_slugs', [ 'term_1' ] )->get();
		$sut->add_user_slug_provider( 'tax_a', $provider );

		Test::assertTrue( $sut->can_access_post() );
	}

	/**
	 * @test
	 * it should allow the user to access the post if the user has more than one access term
	 */
	public function it_should_allow_the_user_to_access_the_post_if_the_user_has_more_than_one_access_term() {
		$sut = new trc_User();

		$post            = new stdClass();
		$post->ID        = 23;
		$post->post_type = 'post';
		Test::replace( 'get_post', $post );

		$taxonomies = Test::replace( 'trc_RestrictingTaxonomiesInterface' )
		                  ->method( 'get_restricting_taxonomies', [ 'tax_a' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		Test::replace( 'wp_get_object_terms', [ 'term_1' ] );

		$provider = Test::replace( 'trc_UserSlugProviderInterface' )->method( 'get_user_slugs', [ 'term_1', 'term_2' ] )
		                ->get();
		$sut->add_user_slug_provider( 'tax_a', $provider );

		Test::assertTrue( $sut->can_access_post() );
	}

	/**
	 * @test
	 * it should allow the user access the post if the user has more than one of the required access terms
	 */
	public function it_should_allow_the_user_access_the_post_if_the_user_has_more_than_one_of_the_required_access_terms() {
		$sut = new trc_User();

		$post            = new stdClass();
		$post->ID        = 23;
		$post->post_type = 'post';
		Test::replace( 'get_post', $post );

		$taxonomies = Test::replace( 'trc_RestrictingTaxonomiesInterface' )
		                  ->method( 'get_restricting_taxonomies', [ 'tax_a' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		Test::replace( 'wp_get_object_terms', [ 'term_1', 'term_2', 'term_3' ] );

		$provider = Test::replace( 'trc_UserSlugProviderInterface' )->method( 'get_user_slugs', [ 'term_1', 'term_2' ] )
		                ->get();
		$sut->add_user_slug_provider( 'tax_a', $provider );

		Test::assertTrue( $sut->can_access_post() );
	}

	/**
	 * @test
	 * it should not allow the user access to the post if the user has not required terms
	 */
	public function it_should_not_allow_the_user_access_to_the_post_if_the_user_has_not_required_terms() {
		$sut = new trc_User();

		$post            = new stdClass();
		$post->ID        = 23;
		$post->post_type = 'post';
		Test::replace( 'get_post', $post );

		$taxonomies = Test::replace( 'trc_RestrictingTaxonomiesInterface' )
		                  ->method( 'get_restricting_taxonomies', [ 'tax_a' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		Test::replace( 'wp_get_object_terms', [ 'term_1', 'term_2' ] );

		$provider = Test::replace( 'trc_UserSlugProviderInterface' )->method( 'get_user_slugs', [ 'term_3' ] )->get();
		$sut->add_user_slug_provider( 'tax_a', $provider );

		Test::assertFalse( $sut->can_access_post() );
	}

	/**
	 * @test
	 * it should allow the user to access the query by default
	 */
	public function it_should_allow_the_user_to_access_the_query_by_default() {
		$sut   = new trc_User();
		$query = Test::replace( 'WP_Query' )->get();

		Test::assertTrue( $sut->can_access_query( $query ) );
	}

	/**
	 * @test
	 * it should allow for the filter to decide if user has access to query or not
	 */
	public function it_should_allow_for_the_filter_to_decide_if_user_has_access_to_query_or_not() {
		$sut = new trc_User();

		Test::replace( 'apply_filters', function ( $tag, $val ) {
			return $tag == 'trc_user_can_access_query' ? false : $val;
		} );

		$query = Test::replace( 'WP_Query' )->get();

		Test::assertFalse( $sut->can_access_query( $query ) );
	}

	/**
	 * @test
	 * it should allow filtering the user access to post
	 */
	public function it_should_allow_filtering_the_user_access_to_post() {
		$sut = new trc_User();

		$post            = new stdClass();
		$post->ID        = 23;
		$post->post_type = 'post';
		Test::replace( 'get_post', $post );

		$taxonomies = Test::replace( 'trc_RestrictingTaxonomiesInterface' )
		                  ->method( 'get_restricting_taxonomies', [ 'tax_a' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		Test::replace( 'wp_get_object_terms', [ 'term_1', 'term_2' ] );

		$provider = Test::replace( 'trc_UserSlugProviderInterface' )->method( 'get_user_slugs', [ 'term_3' ] )->get();
		$sut->add_user_slug_provider( 'tax_a', $provider );

		Test::replace( 'apply_filters', function ( $tag, $val ) {
			return $tag == 'trc_user_can_access_post' ? true : $val;
		} );

		Test::assertTrue( $sut->can_access_post() );
	}

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
}