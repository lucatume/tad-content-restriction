<?php

use tad\FunctionMocker\FunctionMocker as Test;

class HierarchicalTest extends \WP_UnitTestCase {

	protected $backupGlobals = false;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		Test::setUp();

		tests_add_filter( 'pre_get_posts', [ trc_Core_QueryRestrictor::instance(), 'maybe_restrict_query' ] );
		tests_add_filter( 'init', function () {
			register_post_type( 'post_type_1' );
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
	 * it should respect hierarchical taxonomies when restricting
	 */
	public function it_should_respect_hierarchical_taxonomies_when_restricting() {
		activate_plugin( 'tad-restricted-content/tad-restricted-content.php' );

		$tax = 'tax_1';
		register_taxonomy( $tax, [ 'post_type_1' ], [ 'hierarchical' => true ] );

		// term_a
		//      term_a1
		//      term_a2
		// term_b

		$term_a  = wp_insert_term( 'term_a', $tax );
		$term_a1 = wp_insert_term( 'term_a1', $tax, [ 'parent' => $term_a['term_id'] ] );
		$term_a2 = wp_insert_term( 'term_a2', $tax, [ 'parent' => $term_a['term_id'] ] );
		$term_b  = wp_insert_term( 'term_b', $tax );

		$a_restricted  = $this->factory->post->create( [ 'post_type' => 'post_type_1' ] );
		$a1_restricted = $this->factory->post->create( [ 'post_type' => 'post_type_1' ] );
		$a2_restricted = $this->factory->post->create( [ 'post_type' => 'post_type_1' ] );
		$b_restricted  = $this->factory->post->create( [ 'post_type' => 'post_type_1' ] );

		wp_set_object_terms( $a_restricted, [ $term_a['term_id'] ], $tax );
		wp_set_object_terms( $a1_restricted, [ $term_a1['term_id'] ], $tax );
		wp_set_object_terms( $a2_restricted, [ $term_a2['term_id'] ], $tax );
		wp_set_object_terms( $b_restricted, [ $term_b['term_id'] ], $tax );

		$user_slug_provider = Test::replace( 'trc_Public_UserSlugProviderInterface' )
		                          ->method( 'get_taxonomy_name', $tax )
		                          ->method( 'get_user_slugs', [ 'term_a' ] )
		                          ->get();

		trc_Core_Plugin::instance()->post_types->add_restricted_post_type( 'post_type_1' );
		trc_Core_Plugin::instance()->user->add_user_slug_provider( $tax, $user_slug_provider );
		trc_Core_Plugin::instance()->taxonomies->add( $tax );

		$posts    = ( new WP_Query( [ 'post_type' => 'post_type_1' ] ) )->get_posts();
		$post_ids = array_map( function ( $post ) {
			return $post->ID;
		}, $posts );

		Test::assertCount( 3, $posts );
		Test::assertContains( $a_restricted, $post_ids );
		Test::assertContains( $a1_restricted, $post_ids );
		Test::assertContains( $a2_restricted, $post_ids );
		Test::assertNotContains( $b_restricted, $post_ids );
	}
}