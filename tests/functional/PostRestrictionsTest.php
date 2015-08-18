<?php
use tad\FunctionMocker\FunctionMocker as Test;

class trc_Core_PostRestrictionsTest extends \WP_UnitTestCase {

	/**
	 * @var trc_Core_PostRestrictions
	 */
	protected $sut;

	protected $backupGlobals = false;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		Test::setUp();
		$this->sut = trc_Core_PostRestrictions::instance();
	}

	public function tearDown() {
		// your tear down methods here

		// then
		Test::tearDown();
		parent::tearDown();
		$this->reset_taxonomies();
	}

	private function reset_taxonomies() {
		global $wp_taxonomies;
		$wp_taxonomies = [ ];
	}

	/**
	 * @test
	 * it should apply default term resriction to posts
	 */
	public function it_should_apply_default_term_resriction_to_posts() {
		register_post_type( 'post_type_1' );
		register_taxonomy( 'tax_1', 'post_type_1' );

		wp_insert_term( 'term_1', 'tax_1', [ 'slug' => 'term_1' ] );

		$user_slug_provider = Test::replace( 'trc_Public_UserSlugProviderInterface' )
		                          ->method( 'get_default_post_terms', [ 'term_1' ] )->get();
		$this->sut->set_user_slug_provider_for( 'tax_1', $user_slug_provider );

		$posts = $this->factory->post->create_many( 5, [ 'post_type' => 'post_type_1' ] );

		$this->sut->apply_default_restrictions( [ 'tax_1' => $posts ] );

		foreach ( $posts as $id ) {
			$terms = wp_get_object_terms( $id, 'tax_1', [ 'fields' => 'names' ] );
			Test::assertEquals( [ 'term_1' ], $terms );
		}
	}


}