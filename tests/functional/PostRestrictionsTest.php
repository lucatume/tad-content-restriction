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
		$this->sut = new trc_Core_PostRestrictions();
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

	/**
	 * @test
	 * it should not apply any restriction if there are no user slug providers
	 */
	public function it_should_not_apply_any_restriction_if_there_are_no_user_slug_providers() {
		register_post_type( 'post_type_1' );
		register_taxonomy( 'tax_1', 'post_type_1' );

		wp_insert_term( 'term_1', 'tax_1', [ 'slug' => 'term_1' ] );

		$posts = $this->factory->post->create_many( 5, [ 'post_type' => 'post_type_1' ] );

		$this->sut->apply_default_restrictions( [ 'tax_1' => $posts ] );

		foreach ( $posts as $id ) {
			$terms = wp_get_object_terms( $id, 'tax_1', [ 'fields' => 'names' ] );
			Test::assertEmpty( $terms );
		}
	}

	/**
	 * @test
	 * it should not apply any term if no default terms
	 */
	public function it_should_not_apply_any_term_if_no_default_terms() {
		register_post_type( 'post_type_1' );
		register_taxonomy( 'tax_1', 'post_type_1' );

		$user_slug_provider = Test::replace( 'trc_Public_UserSlugProviderInterface' )
		                          ->method( 'get_default_post_terms', [ ] )->get();
		$this->sut->set_user_slug_provider_for( 'tax_1', $user_slug_provider );

		$posts = $this->factory->post->create_many( 5, [ 'post_type' => 'post_type_1' ] );

		$this->sut->apply_default_restrictions( [ 'tax_1' => $posts ] );

		foreach ( $posts as $id ) {
			$terms = wp_get_object_terms( $id, 'tax_1', [ 'fields' => 'names' ] );
			Test::assertEmpty( $terms );
		}
	}

	/**
	 * @test
	 * it should apply default terms for two taxonomies on same post type
	 */
	public function it_should_apply_default_terms_for_two_taxonomies_on_same_post_type() {
		register_post_type( 'post_type_1' );
		register_taxonomy( 'tax_1', 'post_type_1' );
		register_taxonomy( 'tax_2', 'post_type_1' );
		wp_insert_term( 'term_11', 'tax_1', [ 'slug' => 'term_11' ] );
		wp_insert_term( 'term_21', 'tax_2', [ 'slug' => 'term_21' ] );

		$user_slug_provider = Test::replace( 'trc_Public_UserSlugProviderInterface' )
		                          ->method( 'get_default_post_terms', [ 'term_11' ] )->get();
		$this->sut->set_user_slug_provider_for( 'tax_1', $user_slug_provider );
		$user_slug_provider = Test::replace( 'trc_Public_UserSlugProviderInterface' )
		                          ->method( 'get_default_post_terms', [ 'term_21' ] )->get();
		$this->sut->set_user_slug_provider_for( 'tax_2', $user_slug_provider );

		$posts = $this->factory->post->create_many( 5, [ 'post_type' => 'post_type_1' ] );

		$this->sut->apply_default_restrictions( [ 'tax_1' => $posts, 'tax_2' => $posts ] );

		foreach ( $posts as $id ) {
			$tax_1_terms = wp_get_object_terms( $id, 'tax_1', [ 'fields' => 'names' ] );
			Test::assertEquals( [ 'term_11' ], $tax_1_terms );
			$tax_2_terms = wp_get_object_terms( $id, 'tax_2', [ 'fields' => 'names' ] );
			Test::assertEquals( [ 'term_21' ], $tax_2_terms );
		}
	}

	/**
	 * @test
	 * it should apply more than one default term
	 */
	public function it_should_apply_more_than_one_default_term() {
		register_post_type( 'post_type_1' );
		register_taxonomy( 'tax_1', 'post_type_1' );
		wp_insert_term( 'term_11', 'tax_1', [ 'slug' => 'term_11' ] );
		wp_insert_term( 'term_12', 'tax_1', [ 'slug' => 'term_12' ] );
		wp_insert_term( 'term_13', 'tax_1', [ 'slug' => 'term_13' ] );

		$user_slug_provider = Test::replace( 'trc_Public_UserSlugProviderInterface' )
		                          ->method( 'get_default_post_terms', [ 'term_11', 'term_12', 'term_13' ] )->get();
		$this->sut->set_user_slug_provider_for( 'tax_1', $user_slug_provider );

		$posts = $this->factory->post->create_many( 5, [ 'post_type' => 'post_type_1' ] );

		$this->sut->apply_default_restrictions( [ 'tax_1' => $posts ] );

		foreach ( $posts as $id ) {
			$tax_1_terms = wp_get_object_terms( $id, 'tax_1', [ 'fields' => 'names' ] );
			Test::assertEquals( [ 'term_11', 'term_12', 'term_13' ], $tax_1_terms );
		}
	}

	/**
	 * @test
	 * it should apply more than one default term to more post types
	 */
	public function it_should_apply_more_than_one_default_term_to_more_post_types() {
		$post_types = [
			'post_type_1' => [ 'tax_1' => [ 'term_11', 'term_12' ] ],
			'post_type_2' => [ 'tax_1' => [ 'term_11', 'term_12' ] ],
			'post_type_3' => [ 'tax_1' => [ 'term_11', 'term_12' ] ]
		];

		foreach ( array_keys( $post_types ) as $post_type ) {
			register_post_type( $post_type );
			$posts = $this->factory->post->create_many( 5, [ 'post_type' => $post_type ] );
		}

		register_taxonomy( 'tax_1', array_keys( $post_types ) );
		wp_insert_term( 'term_11', 'tax_1', [ 'slug' => 'term_11' ] );
		wp_insert_term( 'term_12', 'tax_1', [ 'slug' => 'term_12' ] );

		$user_slug_provider = Test::replace( 'trc_Public_UserSlugProviderInterface' )
		                          ->method( 'get_default_post_terms', [ 'term_11', 'term_12' ] )->get();
		$this->sut->set_user_slug_provider_for( 'tax_1', $user_slug_provider );


		$this->sut->apply_default_restrictions( [ 'tax_1' => $posts ] );

		foreach ( $posts as $id ) {
			$tax_1_terms = wp_get_object_terms( $id, 'tax_1', [ 'fields' => 'names' ] );
			Test::assertEquals( [ 'term_11', 'term_12' ], $tax_1_terms );
		}
	}

	/**
	 * @test
	 * it should apply terms across many post types and taxonomies
	 */
	public function it_should_apply_terms_across_many_post_types_and_taxonomies() {
		$tax_1_terms = [ 'term_11', 'term_12' ];
		$tax_2_terms = [ 'term_21', 'term_22' ];
		$tax_3_terms = [ 'term_31', 'term_32' ];

		$taxonomies = [ 'tax_1' => $tax_1_terms, 'tax_2' => $tax_2_terms, 'tax_3' => $tax_3_terms ];

		$post_types = [
			'post_type_1' => [ 'tax_1' => $tax_1_terms, 'tax_2' => $tax_2_terms ],
			'post_type_2' => [ 'tax_2' => $tax_2_terms, 'tax_3' => $tax_3_terms ],
			'post_type_3' => [ 'tax_1' => $tax_1_terms, 'tax_3' => $tax_3_terms ]
		];

		$posts = [ ];
		foreach ( array_keys( $post_types ) as $post_type ) {
			register_post_type( $post_type );
			$posts[ $post_type ] = $this->factory->post->create_many( 5, [ 'post_type' => $post_type ] );
		}

		foreach ( $taxonomies as $tax => $terms ) {
			register_taxonomy( $tax, array_keys( $post_types ) );
			$user_slug_provider = Test::replace( 'trc_Public_UserSlugProviderInterface' )
			                          ->method( 'get_default_post_terms', $terms )->get();
			$this->sut->set_user_slug_provider_for( $tax, $user_slug_provider );
			foreach ( $terms as $term ) {
				wp_insert_term( $term, $tax, [ 'slug' => $term ] );
			}
		}

		$this->sut->apply_default_restrictions( [
			'tax_1' => array_merge( $posts['post_type_1'], $posts['post_type_3'] ),
			'tax_2' => array_merge( $posts['post_type_1'], $posts['post_type_2'] ),
			'tax_3' => array_merge( $posts['post_type_2'], $posts['post_type_3'] )
		] );

		foreach ( $post_types as $post_type => $taxonomies ) {
			foreach ( $posts[ $post_type ] as $id ) {
				foreach ( $taxonomies as $tax => $terms ) {
					$applied_terms = wp_get_object_terms( $id, $tax, [ 'fields' => 'names' ] );
					Test::assertEquals( $terms, $applied_terms );
				}
			}
		}
	}
}