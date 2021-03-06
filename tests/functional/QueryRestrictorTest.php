<?php


use tad\FunctionMocker\FunctionMocker as Test;

class QueryRestrictorTest extends \WP_UnitTestCase {

	protected $backupGlobals = false;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		Test::setUp();

		$user = Test::replace( 'WP_User' )
		            ->get();
		Test::replace( 'get_user_by', $user );

		tests_add_filter( 'pre_get_posts', [ trc_Core_QueryRestrictor::instance(), 'maybe_restrict_query' ] );
		tests_add_filter( 'init', function () {
			register_post_type( 'post_type_1' );
			register_post_type( 'post_type_2' );
			register_post_type( 'post_type_3' );
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
	 * it should restrict queries with one restriction taxonomy
	 */
	public function it_should_restrict_queries_with_one_restriction_taxonomy() {
		activate_plugin( 'tad-restricted-content/tad-restricted-content.php' );

		$accessible_id   = $this->factory->post->create();
		$unaccessible_id = $this->factory->post->create();

		$tax_name = 'tax_1';
		register_taxonomy( $tax_name, 'post' );
		wp_insert_term( 'term_1', $tax_name );
		wp_insert_term( 'term_2', $tax_name );

		wp_set_object_terms( $accessible_id, 'term_1', $tax_name );
		wp_set_object_terms( $unaccessible_id, 'term_2', $tax_name );

		trc_Core_Plugin::instance()->taxonomies->add( $tax_name );

		$user_slug_provider = Test::replace( 'trc_Public_UserSlugProviderInterface' )
		                          ->method( 'get_user_slugs', [ 'term_1' ] )
		                          ->get();
		trc_Core_Plugin::instance()->user->add_user_slug_provider( $tax_name, $user_slug_provider );

		$posts = ( new WP_Query( [ 'post_type' => 'post' ] ) )->get_posts();

		Test::assertCount( 1, $posts );
		Test::assertEquals( $accessible_id, $posts[0]->ID );
	}

	/**
	 * @test
	 * it should restrict queries with two restriction taxonomies
	 */
	public function it_should_restrict_queries_with_two_restriction_taxonomies() {
		activate_plugin( 'tad-restricted-content/tad-restricted-content.php' );

		$accessible_id   = $this->factory->post->create();
		$unaccessible_id = $this->factory->post->create();

		$tax_name = 'tax_1';
		register_taxonomy( $tax_name, 'post' );
		wp_insert_term( 'term_11', $tax_name );
		wp_insert_term( 'term_12', $tax_name );

		$tax_name = 'tax_2';
		register_taxonomy( $tax_name, 'post' );
		wp_insert_term( 'term_21', $tax_name );
		wp_insert_term( 'term_22', $tax_name );

		wp_set_object_terms( $accessible_id, 'term_11', 'tax_1' );
		wp_set_object_terms( $accessible_id, 'term_12', 'tax_2' );
		wp_set_object_terms( $unaccessible_id, 'term_21', 'tax_1' );
		wp_set_object_terms( $unaccessible_id, 'term_22', 'tax_2' );

		trc_Core_Plugin::instance()->taxonomies->add( 'tax_1' );
		trc_Core_Plugin::instance()->taxonomies->add( 'tax_2' );

		$user_slug_provider = Test::replace( 'trc_Public_UserSlugProviderInterface' )
		                          ->method( 'get_user_slugs', 'term_11' )
		                          ->get();
		trc_Core_Plugin::instance()->user->add_user_slug_provider( 'tax_1', $user_slug_provider );

		$user_slug_provider = Test::replace( 'trc_Public_UserSlugProviderInterface' )
		                          ->method( 'get_user_slugs', 'term_12' )
		                          ->get();
		trc_Core_Plugin::instance()->user->add_user_slug_provider( 'tax_2', $user_slug_provider );

		$posts = ( new WP_Query( [ 'post_type' => 'post' ] ) )->get_posts();

		Test::assertCount( 1, $posts );
		Test::assertEquals( $accessible_id, $posts[0]->ID );
	}

	/**
	 * @test
	 * it should allow restricting access to custom post types
	 */
	public function it_should_allow_restricting_access_to_custom_post_types() {
		activate_plugin( 'tad-restricted-content/tad-restricted-content.php' );

		$accessible   = $this->factory->post->create_and_get( [ 'post_type' => 'post_type_1' ] );
		$unaccessible = $this->factory->post->create_and_get( [ 'post_type' => 'post_type_1' ] );

		$tax_name = 'tax_1';
		register_taxonomy( $tax_name, 'post_type_1' );
		wp_insert_term( 'term_1', $tax_name );
		wp_insert_term( 'term_2', $tax_name );

		wp_set_object_terms( $accessible->ID, 'term_1', 'tax_1' );
		wp_set_object_terms( $unaccessible->ID, 'term_2', 'tax_1' );

		trc_Core_Plugin::instance()->taxonomies->add( 'tax_1' );
		trc_Core_Plugin::instance()->post_types->add_restricted_post_type( 'post_type_1' );

		$user_slug_provider = Test::replace( 'trc_Public_UserSlugProviderInterface' )
		                          ->method( 'get_user_slugs', 'term_1' )
		                          ->get();
		trc_Core_Plugin::instance()->user->add_user_slug_provider( 'tax_1', $user_slug_provider );

		$posts = ( new WP_Query( [ 'post_type' => 'post_type_1' ] ) )->get_posts();

		Test::assertCount( 1, $posts );
		Test::assertEquals( $accessible, $posts[0] );
	}

}