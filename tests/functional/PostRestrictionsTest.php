<?php


class trc_Core_PostRestrictionsTest extends trc_Core_PostDefaultsTest {

	protected $backupGlobals = false;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
		$this->reset_taxonomies();
	}

	/**
	 * @test
	 * it should apply default term resriction to posts
	 */
	public function it_should_apply_default_term_resriction_to_posts() {
		register_post_type( 'post_type_1' );
		register_taxonomy( 'tax_1', 'post_type_1' );

		wp_insert_term( 'term_1', 'tax_1', [ 'slug' => 'term_1' ] );


	}

}