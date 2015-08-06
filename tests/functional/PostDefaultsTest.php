<?php


use tad\FunctionMocker\FunctionMocker as Test;

class trc_Core_PostDefaultsTest extends \WP_UnitTestCase {

	protected $backupGlobals = false;

	/** @var  trc_Core_PostDefaults */
	protected $sut;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		Test::setUp();
		$this->sut = new trc_Core_PostDefaults();
	}

	public function tearDown() {
		// your tear down methods here
		Test::tearDown();

		// then
		parent::tearDown();
	}

	public function oneTaxTerms() {
		return [
			[ [ 'term_one' ] ],
			[
				[
					'term_one',
					'term_two'
				]
			],
			[
				[
					'term_one',
					'term_two',
					'term_three'
				]
			],
			[ [ ] ],
		];
	}

	/**
	 * @test
	 * it should apply default terms for a taxonomy when inserting a post
	 * @dataProvider oneTaxTerms
	 */
	public function it_should_apply_default_terms_for_a_taxonomy_when_inserting_a_post( $terms ) {
		$tax = 'tax_one';
		register_taxonomy( $tax, 'post' );

		foreach ( $terms as $term ) {
			wp_insert_term( $term, $tax, [ 'slug' => $term ] );
		}
		$this->sut->hook();

		$user_slug_provider = Test::replace( 'trc_Public_UserSlugProviderInterface' )
		                          ->method( 'get_default_post_terms', $terms )->get();
		$this->sut->set_user_slug_provider_for( $tax, $user_slug_provider );

		$id = wp_insert_post( [
			'post_title' => 'A new post',
			'post_type'  => 'post'
		] );

		$_terms = wp_get_object_terms( $id, $tax );
		$this->assertCount( count( $terms ), $_terms );

		$_slugs = sort( wp_list_pluck( $_terms, 'slug' ) );
		$this->assertEquals( $_slugs, sort( $terms ) );
	}

	public function twoTaxonomiesTerms() {
		return array_map( function ( $v ) {
			return [ $v ];
		}, [
			[
				'tax_1' => [ ],
				'tax_2' => [ ]
			],
			[
				'tax_1' => [ 'term_one' ],
				'tax_2' => [ ]
			],
			[
				'tax_1' => [
					'term_one',
					'term_two',
					'term_three'
				],
				'tax_2' => [ ]
			],
			[
				'tax_1' => [
					'term_one',
					'term_two',
					'term_three'
				],
				'tax_2' => [ 'term_four' ]
			],
			[
				'tax_1' => [
					'term_one',
					'term_two',
					'term_three'
				],
				'tax_2' => [
					'term_four',
					'term_five'
				]
			],
			[
				'tax_1' => [
					'term_one',
					'term_two',
					'term_three'
				],
				'tax_2' => [
					'term_four',
					'term_five',
					'term_six'
				]
			],
			[
				'tax_1' => [
					'term_one',
					'term_two'
				],
				'tax_2' => [
					'term_four',
					'term_five',
					'term_six'
				]
			],
			[
				'tax_1' => [ 'term_one' ],
				'tax_2' => [
					'term_four',
					'term_five',
					'term_six'
				]
			],
			[
				'tax_1' => [ ],
				'tax_2' => [
					'term_four',
					'term_five',
					'term_six'
				]
			],
			[
				'tax_1' => [ ],
				'tax_2' => [
					'term_four',
					'term_five'
				]
			],
			[
				'tax_1' => [ ],
				'tax_2' => [ 'term_four' ]
			],
			[
				'tax_1' => [ ],
				'tax_2' => [ 'term_four' ]
			],
		] );
	}

	/**
	 * @test
	 * it should apply default terms for two taxonomies
	 * @dataProvider twoTaxonomiesTerms
	 */
	public function it_should_apply_default_terms_for_two_taxonomies( $_terms ) {
		foreach ( $_terms as $tax => $terms ) {
			register_taxonomy( $tax, 'post' );

			foreach ( $terms as $term ) {
				wp_insert_term( $term, $tax, [ 'slug' => $term ] );
			}

			$user_slug_provider = Test::replace( 'trc_Public_UserSlugProviderInterface' )
			                          ->method( 'get_default_post_terms', $terms )->get();
			$this->sut->set_user_slug_provider_for( $tax, $user_slug_provider );
		}

		$this->sut->hook();

		$id = wp_insert_post( [
			'post_title' => 'A new post',
			'post_type'  => 'post'
		] );

		foreach ( $_terms as $tax => $terms ) {
			$o_terms = wp_get_object_terms( $id, $tax );
			$this->assertCount( count( $terms ), $o_terms );

			$_slugs = sort( wp_list_pluck( $o_terms, 'slug' ) );
			$this->assertEquals( $_slugs, sort( $terms ) );
		}
	}

	/**
	 * @test
	 * it should not apply any term if restricting taxonomies do not provide any default term
	 */
	public function it_should_not_apply_any_term_if_restricting_taxonomies_do_not_provide_any_default_term() {
		$_terms = [
			'tax_1' => [ ],
			'tax_2' => [ ]
		];
		foreach ( $_terms as $tax => $terms ) {
			register_taxonomy( $tax, 'post' );

			foreach ( $terms as $term ) {
				wp_insert_term( $term, $tax, [ 'slug' => $term ] );
			}

			$user_slug_provider = Test::replace( 'trc_Public_UserSlugProviderInterface' )
			                          ->method( 'get_default_post_terms', [ ] )->get();
			$this->sut->set_user_slug_provider_for( $tax, $user_slug_provider );
		}

		$this->sut->hook();

		$id = wp_insert_post( [
			'post_title' => 'A new post',
			'post_type'  => 'post'
		] );

		foreach ( $_terms as $tax => $terms ) {
			$o_terms = wp_get_object_terms( $id, $tax );
			$this->assertCount( count( $terms ), $o_terms );

			$_slugs = sort( wp_list_pluck( $o_terms, 'slug' ) );
			$this->assertEquals( $_slugs, sort( $terms ) );
		}
	}

	/**
	 * @test
	 * it should fetch not restricted post types
	 */
	public function it_should_fetch_not_restricted_post_types() {
		register_taxonomy( 'tax_1', 'post' );
		wp_insert_term( 'term_1', 'tax_1', [ 'slug' => 'term_1' ] );

		$this->factory->post->create_many( 10 );

		$user_slug_provider = Test::replace( 'trc_Public_UserSlugProviderInterface' )
		                          ->method( 'get_default_post_terms', [ 'term_1' ] )->get();
		$this->sut->set_user_slug_provider_for( 'tax_1', $user_slug_provider );

		$ids = $this->sut->fetch_posts_with_no_default_restriction( 'post', 'tax_1' );

		Test::assertCount( 10, $ids );
	}

	/**
	 * @test
	 * it should return posts without terms only
	 */
	public function it_should_return_posts_without_terms_only() {
		register_taxonomy( 'tax_1', 'post' );
		wp_insert_term( 'term_1', 'tax_1', [ 'slug' => 'term_1' ] );

		$this->factory->post->create_many( 5 );
		$posts = $this->factory->post->create_many( 5 );
		for ( $i = 0; $i < 5; $i ++ ) {
			wp_set_object_terms( $posts[ $i ], 'term_1', 'tax_1', false );
		}

		$user_slug_provider = Test::replace( 'trc_Public_UserSlugProviderInterface' )
		                          ->method( 'get_default_post_terms', [ 'term_1' ] )->get();
		$this->sut->set_user_slug_provider_for( 'tax_1', $user_slug_provider );

		$ids = $this->sut->fetch_posts_with_no_default_restriction( 'post', 'tax_1' );

		Test::assertCount( 5, $ids );
	}

	/**
	 * @test
	 * it should return empty array if all posts have restriction applied
	 */
	public function it_should_return_empty_array_if_all_posts_have_restriction_applied() {
		register_taxonomy( 'tax_1', 'post' );
		wp_insert_term( 'term_1', 'tax_1', [ 'slug' => 'term_1' ] );

		$posts = $this->factory->post->create_many( 10 );
		for ( $i = 0; $i < 10; $i ++ ) {
			wp_set_object_terms( $posts[ $i ], 'term_1', 'tax_1', false );
		}

		$user_slug_provider = Test::replace( 'trc_Public_UserSlugProviderInterface' )
		                          ->method( 'get_default_post_terms', [ 'term_1' ] )->get();
		$this->sut->set_user_slug_provider_for( 'tax_1', $user_slug_provider );

		$ids = $this->sut->fetch_posts_with_no_default_restriction( 'post', 'tax_1' );

		Test::assertCount( 0, $ids );
	}

	/**
	 * @test
	 * it should retrieve posts in tax key/value array when tax not specified
	 */
	public function it_should_retrieve_posts_in_tax_key_value_array_when_tax_not_specified() {
		register_taxonomy( 'tax_1', 'post' );
		register_taxonomy( 'tax_2', 'post' );
		wp_insert_term( 'term_1', 'tax_1', [ 'slug' => 'term_1' ] );
		wp_insert_term( 'term_2', 'tax_2', [ 'slug' => 'term_2' ] );

		$posts = $this->factory->post->create_many( 10 );
		for ( $i = 0; $i < 5; $i ++ ) {
			wp_set_object_terms( $posts[ $i ], 'term_1', 'tax_1', false );
		}
		for ( $i = 0; $i < 5; $i ++ ) {
			wp_set_object_terms( $posts[ $i ], 'term_2', 'tax_2', false );
		}
		$restricted = array_splice( $posts, 0, 5 );

		$user_slug_provider_1 = Test::replace( 'trc_Public_UserSlugProviderInterface' )
		                            ->method( 'get_default_post_terms', [ 'term_1' ] )->get();
		$this->sut->set_user_slug_provider_for( 'tax_1', $user_slug_provider_1 );
		$user_slug_provider_2 = Test::replace( 'trc_Public_UserSlugProviderInterface' )
		                            ->method( 'get_default_post_terms', [ 'term_2' ] )->get();
		$this->sut->set_user_slug_provider_for( 'tax_2', $user_slug_provider_2 );

		$ids = $this->sut->fetch_posts_with_no_default_restriction( 'post' );

		Test::assertEquals( $posts, $ids['tax_1'] );
		Test::assertEquals( $posts, $ids['tax_2'] );
	}
}