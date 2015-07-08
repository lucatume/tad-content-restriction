<?php

use tad\FunctionMocker\FunctionMocker as Test;

class MixedRestrictionPostTypesTest extends \WP_UnitTestCase {

	protected $backupGlobals = false;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		Test::setUp();

		tests_add_filter( 'pre_get_posts', [ trc_Core_QueryRestrictor::instance(), 'maybe_restrict_query' ] );
		tests_add_filter( 'init', function () {
			register_post_type( 'post_type_1' );
			register_post_type( 'post_type_2' );
		} );
	}

	public function tearDown() {
		// your tear down methods here
		Test::tearDown();

		// then
		parent::tearDown();
	}

	/**
	 * @test
	 * it should allow querying restricted and unrestricted post types
	 */
	public function it_should_allow_querying_restricted_and_unrestricted_post_types() {
		activate_plugin( 'tad-restricted-content/tad-restricted-content.php' );

		$restricted_accessible   = $this->factory->post->create_many( 10, [ 'post_type' => 'post_type_1' ] );
		$restricted_unaccessible = $this->factory->post->create_many( 10, [ 'post_type' => 'post_type_1' ] );
		$this->factory->post->create_many( 10, [ 'post_type' => 'post_type_2' ] );

		register_taxonomy( 'tax_1', 'post_type_1' );
		wp_insert_term( 'term_1', 'tax_1' );
		wp_insert_term( 'term_2', 'tax_1' );

		$slug_provider = Test::replace( 'trc_Public_UserSlugProviderInterface' )
		                     ->method( 'get_taxonomy_name', 'tax_1' )
		                     ->method( 'get_user_slugs', [ 'term_1' ] )
		                     ->get();

		trc_Core_Plugin::instance()->post_types->add_restricted_post_type( 'post_type_1' );
		trc_Core_Plugin::instance()->taxonomies->add( 'tax_1' );
		trc_Core_Plugin::instance()->user->add_user_slug_provider( 'tax_1', $slug_provider );

		array_map( function ( $post ) {
			wp_set_object_terms( $post, [ 'term_1', 'term_2' ], 'tax_1' );
		}, $restricted_accessible );

		array_map( function ( $post ) {
			wp_set_object_terms( $post, 'term_2', 'tax_1' );
		}, $restricted_unaccessible );

		$posts = ( new WP_Query( [
			'post_type' => [ 'post_type_1', 'post_type_2' ],
			'nopaging'  => true
		] ) )->get_posts();

		Test::assertCount( 20, $posts );
		Test::assertCount( 10, array_filter( $posts, function ( $post ) {
			return $post->post_type == 'post_type_1';
		} ) );
		Test::assertCount( 10, array_filter( $posts, function ( $post ) {
			return $post->post_type == 'post_type_2';
		} ) );
		$post_type_2s = get_posts( [
			'post_type' => 'post_type_1',
			'tax_query' => [
				[
					'taxonomy' => 'tax_1',
					'fields'   => 'slugs',
					'terms'    => 'term_1'
				]
			]
		] );
		Test::assertArraySubset( $post_type_2s, $posts );
	}

}