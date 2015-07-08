<?php
use tad\FunctionMocker\FunctionMocker as Test;

class TemplateRedirectorTest extends \WP_UnitTestCase {

	protected $backupGlobals = false;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		Test::setUp();

		$user = Test::replace( 'WP_User' )->get();
		Test::replace( 'get_user_by', $user );

		tests_add_filter( 'pre_get_posts', [ trc_Core_QueryRestrictor::instance(), 'maybe_restrict_query' ] );
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
	 * it should be able to redirect user that has no access to post
	 */
	public function it_should_be_able_to_redirect_user_that_has_no_access_to_post() {
		activate_plugin( 'tad-restricted-content/tad-restricted-content.php' );

		$post = $this->factory->post->create_and_get();

		$tax_name = 'tax_1';
		register_taxonomy( $tax_name, 'post' );
		wp_insert_term( 'term_1', $tax_name );
		wp_insert_term( 'term_2', $tax_name );

		wp_set_object_terms( $post->ID, 'term_2', $tax_name );

		trc_Core_Plugin::instance()->taxonomies->add( $tax_name );

		$user_slug_provider = Test::replace( 'trc_Public_UserSlugProviderInterface' )
		                          ->method( 'get_user_slugs', [ 'term_1' ] )->get();
		trc_Core_Plugin::instance()->user->add_user_slug_provider( $tax_name, $user_slug_provider );

		$sut = trc_Core_TemplateRedirector::instance();

		Test::replace( 'get_post', $post );
		Test::replace( 'locate_template', 'redirected.php' );
		Test::replace( 'get_taxonomies', [ 'tax_1' => 23 ] );
		Test::replace( 'is_singular', true );

		Test::assertEquals( 'redirected.php', $sut->maybe_redirect( 'single.php' ) );
	}

	/**
	 * @test
	 * it should allow restricting access to custom post types templates
	 */
	public function it_should_allow_restricting_access_to_custom_post_types_templates() {
		activate_plugin( 'tad-restricted-content/tad-restricted-content.php' );

		$post = $this->factory->post->create_and_get( [ 'post_type' => 'notice' ] );

		$tax_name = 'tax_1';
		register_taxonomy( $tax_name, 'notice' );
		wp_insert_term( 'term_1', $tax_name );
		wp_insert_term( 'term_2', $tax_name );

		wp_set_object_terms( $post->ID, 'term_2', $tax_name );

		trc_Core_Plugin::instance()->taxonomies->add( $tax_name );
		trc_Core_Plugin::instance()->post_types->add_restricted_post_type( 'notice' );

		$user_slug_provider = Test::replace( 'trc_Public_UserSlugProviderInterface' )
		                          ->method( 'get_user_slugs', [ 'term_1' ] )->get();
		trc_Core_Plugin::instance()->user->add_user_slug_provider( $tax_name, $user_slug_provider );

		$sut = trc_Core_TemplateRedirector::instance();

		Test::replace( 'get_post', $post );
		Test::replace( 'locate_template', 'redirected.php' );
		Test::replace( 'get_taxonomies', [ 'tax_1' => 23 ] );
		Test::replace( 'is_singular', true );

		Test::assertEquals( 'redirected.php', $sut->maybe_redirect( 'single.php' ) );
	}
}