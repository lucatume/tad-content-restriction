<?php

use tad\FunctionMocker\FunctionMocker as Test;

class FiltersTest extends \WP_UnitTestCase {

	protected $backupGlobals = false;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		Test::setUp();

		tests_add_filter( 'init', function () {
			register_post_type( 'notice' );
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
	 * it should allow setting up restriction using filters only
	 */
	public function it_should_allow_setting_up_restriction_using_filters_only() {
		add_filter( 'trc_post_type_restricting_taxonomies', function () {
			return [ 'tax_1' ];
		} );
		add_filter( 'trc_restricted_post_types', function () {
			return [ 'notice' ];
		} );
		add_filter( 'trc_user_slugs_for', function ( $slugs, $tax ) {
			if ( $tax != 'tax_1' ) {
				return $slugs;
			}

			return is_user_logged_in() ? [ 'yes', 'no' ] : [ 'no' ];
		}, 10, 2 );

		Test::replace( 'is_user_logged_in', false );

		$notice_1 = $this->factory->post->create( [ 'post_type' => 'notice' ] );
		$notice_2 = $this->factory->post->create( [ 'post_type' => 'notice' ] );

		register_taxonomy( 'tax_1', 'notice' );
		wp_insert_term( 'yes', 'tax_1' );
		wp_insert_term( 'no', 'tax_1' );

		// notice_2 accessible to logged in users only
		wp_set_object_terms( $notice_1, [ 'no', 'yes' ], 'tax_1' );
		wp_set_object_terms( $notice_2, [ 'yes' ], 'tax_1' );

		trc_Core_QueryRestrictor::instance()->init();

		$posts = ( new WP_Query( [ 'post_type' => 'notice' ] ) )->get_posts();

		Test::assertCount( 1, $posts );
		Test::assertEquals( $notice_1, $posts[0]->ID );
	}
}