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

	public function taxonomiesProvider() {
		return [
			[ [ 'tax_1' => [ 'term_1' ] ] ],
			[ [ 'tax_1' => [ 'term_1', 'term_2' ] ] ],
			[ [ 'tax_1' => [ 'term_1', 'term_2', 'term_3' ] ] ],
			[ [ 'tax_1' => [ 'term_1', 'term_2', 'term_3' ], 'tax_2' => [ 'term_4', 'term_5', 'term_6' ] ] ],
			[ [ 'tax_1' => [ 'term_1', 'term_2' ], 'tax_2' => [ 'term_4', 'term_5', 'term_6' ] ] ],
			[ [ 'tax_1' => [ 'term_1' ], 'tax_2' => [ 'term_4' ], 'tax_3' => [ 'term_7' ] ] ]
		];
	}

	/**
	 * @test
	 * it should asssert if there are posts with no default restriction applied
	 * @dataProvider taxonomiesProvider
	 */
	public function it_should_asssert_if_there_are_posts_with_no_default_restriction_applied( $taxonomies ) {
		foreach ( $taxonomies as $tax => $terms ) {
			register_taxonomy( $tax, 'post' );
			foreach ( $terms as $t ) {
				wp_insert_term( $t, $tax, [ 'slug' => $t ] );
			}
			$user_slug_provider = Test::replace( 'trc_Public_UserSlugProviderInterface' )
			                          ->method( 'get_default_post_terms', $terms )
			                          ->get();
			$this->sut->set_user_slug_provider_for( $tax, $user_slug_provider );
		}

		$posts = $this->factory->post->create_many( 10 );

		Test::assertTrue( $this->sut->has_unrestricted_posts() );
	}

	/**
	 * @test
	 * it should assert there are no unrestricted posts
	 * @dataProvider taxonomiesProvider
	 */
	public function it_should_assert_there_are_no_unrestricted_posts( $taxonomies ) {
		foreach ( $taxonomies as $tax => $terms ) {
			register_taxonomy( $tax, 'post' );
			foreach ( $terms as $t ) {
				wp_insert_term( $t, $tax, [ 'slug' => $t ] );
			}
			$user_slug_provider = Test::replace( 'trc_Public_UserSlugProviderInterface' )
			                          ->method( 'get_default_post_terms', $terms )
			                          ->get();
			$this->sut->set_user_slug_provider_for( $tax, $user_slug_provider );
		}

		$posts = $this->factory->post->create_many( 5 );

		foreach ( $taxonomies as $tax => $terms ) {
			foreach ( $posts as $p ) {
				wp_set_object_terms( $p, $terms, $tax );
			}
		}

		Test::assertFalse( $this->sut->has_unrestricted_posts() );
	}

	public function emptyTaxonomies() {
		return [
			[ 'tax_1' => [ ] ],
			[ 'tax_1' => [ ], 'tax_2' => [ ] ],
			[ 'tax_1' => [ ], 'tax_2' => [ ], 'tax_3' => [ ] ],
		];
	}


	/**
	 * @test
	 * it should assert there are no unrestricted posts when taxonomies do not have default term
	 * @dataProvider emptyTaxonomies
	 */
	public function it_should_assert_there_are_no_unrestricted_posts_when_taxonomies_do_not_have_default_term( $taxonomies ) {
		foreach ( $taxonomies as $tax => $terms ) {
			register_taxonomy( $tax, 'post' );
			foreach ( $terms as $t ) {
				wp_insert_term( $t, $tax, [ 'slug' => $t ] );
			}
			$user_slug_provider = Test::replace( 'trc_Public_UserSlugProviderInterface' )
			                          ->method( 'get_default_post_terms', $terms )
			                          ->get();
			$this->sut->set_user_slug_provider_for( $tax, $user_slug_provider );
		}

		$posts = $this->factory->post->create_many( 5 );

		foreach ( $taxonomies as $tax => $terms ) {
			foreach ( $posts as $p ) {
				wp_set_object_terms( $p, $terms, $tax );
			}
		}

		Test::assertFalse( $this->sut->has_unrestricted_posts() );
	}

	public function mixedTaxonomies() {
		return [
			[ [ 'tax_1' => [ ], 'tax_2' => [ 'term_2' ] ] ],
			[ [ 'tax_1' => [ ], 'tax_2' => [ 'term_2' ], 'tax_3' => [ 'term_3' ] ] ],
			[ [ 'tax_1' => [ ], 'tax_2' => [ ], 'tax_3' => [ 'term_3' ] ] ],
			[ [ 'tax_1' => [ ], 'tax_2' => [ 'term_2', 'term_3' ], 'tax_3' => [ 'term_4' ] ] ]
		];
	}

	/**
	 * @test
	 * it should assert there are unrestricted posts in has and has not default term scenario
	 * @dataProvider mixedTaxonomies
	 */
	public function it_should_assert_there_are_unrestricted_posts_in_has_and_has_not_default_term_scenario( $taxonomies ) {
		foreach ( $taxonomies as $tax => $terms ) {
			register_taxonomy( $tax, 'post' );
			foreach ( $terms as $t ) {
				wp_insert_term( $t, $tax, [ 'slug' => $t ] );
			}
			$user_slug_provider = Test::replace( 'trc_Public_UserSlugProviderInterface' )
			                          ->method( 'get_default_post_terms', $terms )
			                          ->get();
			$this->sut->set_user_slug_provider_for( $tax, $user_slug_provider );
		}

		$posts = $this->factory->post->create_many( 10 );

		Test::assertTrue( $this->sut->has_unrestricted_posts() );
	}

	/**
	 * @test
	 * it should assert there are not unrestricted posts in has and has not default term scenario
	 * @dataProvider mixedTaxonomies
	 */
	public function it_should_assert_there_are_not_unrestricted_posts_in_has_and_has_not_default_term_scenario( $taxonomies ) {
		foreach ( $taxonomies as $tax => $terms ) {
			register_taxonomy( $tax, 'post' );
			foreach ( $terms as $t ) {
				wp_insert_term( $t, $tax, [ 'slug' => $t ] );
			}
			$user_slug_provider = Test::replace( 'trc_Public_UserSlugProviderInterface' )
			                          ->method( 'get_default_post_terms', $terms )
			                          ->get();
			$this->sut->set_user_slug_provider_for( $tax, $user_slug_provider );
		}

		$posts = $this->factory->post->create_many( 5 );

		foreach ( $taxonomies as $tax => $terms ) {
			foreach ( $posts as $p ) {
				wp_set_object_terms( $p, $terms, $tax );
			}
		}

		Test::assertFalse( $this->sut->has_unrestricted_posts() );
	}

	public function postTypesAndTaxonomies() {
		return [
			[ [ 'post_type_1' => [ 'tax_1' => [ 'term_11' ] ] ], true ],
			[ [ 'post_type_1' => [ 'tax_1' => [ 'term_11' ], 'tax_2' => [ 'term_21' ] ] ], true ],
			[
				[
					'post_type_1' => [ 'tax_1' => [ 'term_11' ] ],
					'post_type_2' => [ 'tax_1' => [ 'term_11' ] ]
				],
				true
			],
			[
				[
					'post_type_1' => [ 'tax_1' => [ 'term_11' ] ],
					'post_type_2' => [ 'tax_1' => [ 'term_11' ] ]
				],
				true
			],
			[
				[
					'post_type_1' => [ 'tax_1' => [ ] ],
					'post_type_2' => [ 'tax_1' => [ ] ]
				],
				false
			],
			[
				[
					'post_type_1' => [ 'tax_1' => [ ] ],
					'post_type_2' => [ 'tax_2' => [ ] ]
				],
				false
			],
			[
				[
					'post_type_1' => [ 'tax_1' => [ ], 'tax_2' => [ 'term_21' ] ],
					'post_type_2' => [ 'tax_3' => [ ] ]
				],
				true
			],
			[
				[
					'post_type_1' => [ 'tax_1' => [ ], 'tax_2' => [ 'term_21' ] ],
					'post_type_2' => [ 'tax_3' => [ ] ],
					'post_type_3' => [ 'tax_4' => [ 'term_41' ] ]
				],
				true
			],
			[
				[
					'post_type_1' => [ 'tax_1' => [ ], 'tax_2' => [ ] ],
					'post_type_2' => [ 'tax_3' => [ ] ],
					'post_type_3' => [ 'tax_4' => [ ] ]
				],
				false
			],
			[
				[
					'post_type_1' => [ 'tax_1' => [ 'term_11' ], 'tax_2' => [ 'term_21' ] ],
					'post_type_2' => [ 'tax_3' => [ 'term_31' ] ],
					'post_type_3' => [ 'tax_4' => [ 'term_41' ] ]
				],
				true
			],
		];
	}

	/**
	 * @test
	 * it should assert unrestricted posts in multiple post/tax scenarios
	 * @dataProvider postTypesAndTaxonomies
	 */
	public function it_should_assert_unrestricted_posts_in_multiple_post_tax_scenarios( $post_types, $has ) {
		foreach ( $post_types as $pt => $taxonomies ) {
			register_post_type( $pt );
			foreach ( $taxonomies as $tax => $terms ) {
				register_taxonomy( $tax, [ ] );
				register_taxonomy_for_object_type( $tax, $pt );
				foreach ( $terms as $t ) {
					wp_insert_term( $t, $tax, [ 'slug' => $t ] );
				}
				$user_slug_provider = Test::replace( 'trc_Public_UserSlugProviderInterface' )
				                          ->method( 'get_default_post_terms', $terms )
				                          ->get();
				$this->sut->set_user_slug_provider_for( $tax, $user_slug_provider );
			}

			$this->factory->post->create_many( 5, [ 'post_type' => $pt ] );
		}

		Test::assertEquals( $has, $this->sut->has_unrestricted_posts() );
	}

//* it should return an array of existing post IDs
//* it should return up to a finite amount of existing post IDs, say 100, that's safe to process without clogging the server
//* it should return an empty array if there are no posts that require a default restriction
//* it should take an optional parameter, `post_type`, to return post IDs of that type only
	public function unrestrictedPostCombos() {
		return [
			[ [  ], 'post', 5 ],
			[ [ 'tax_1' => [ ], 'tax_2' => [  ] ], 'post', 5 ],
			[ [ 'tax_1' => ['term_11' ], 'tax_2' => [  ] ], 'post', 5 ],
			[ [ 'tax_1' => ['term_11','term_12' ], 'tax_2' => ['term_21'  ] ], 'post', 5 ],
			[ [ 'tax_1' => ['term_11','term_12' ], 'tax_2' => ['term_21','term_22'  ] ], 'post', 5 ],
			[ [ 'tax_1' => ['term_11','term_12' ], 'tax_2' => ['term_21','term_23'  ],'tax_3' => ['term_31','term_33'  ] ], 'post', 5 ],
			[ [ 'tax_1' => [], 'tax_2' => [],'tax_3' => ['term_31','term_33'  ] ], 'post', 5 ],
			[ [ 'tax_1' => [ 'term_11' ], 'tax_2' => [ 'term_21' ] ], 'post', 5 ],
			[ [  ], 'post', 0 ],
			[ [ 'tax_1' => [ ], 'tax_2' => [  ] ], 'post', 0 ],
			[ [ 'tax_1' => ['term_11' ], 'tax_2' => [  ] ], 'post', 0 ],
			[ [ 'tax_1' => ['term_11','term_12' ], 'tax_2' => ['term_21'  ] ], 'post', 0 ],
			[ [ 'tax_1' => ['term_11','term_12' ], 'tax_2' => ['term_21','term_22'  ] ], 'post', 0 ],
			[ [ 'tax_1' => ['term_11','term_12' ], 'tax_2' => ['term_21','term_23'  ],'tax_3' => ['term_31','term_33'  ] ], 'post', 0 ],
			[ [ 'tax_1' => [], 'tax_2' => [],'tax_3' => ['term_31','term_33'  ] ], 'post', 0 ],
			[ [ 'tax_1' => [ 'term_11' ], 'tax_2' => [ 'term_21' ] ], 'post', 0 ],
		];
	}

	/**
	 * @test
	 * it should return an array of existing unrestricted post IDs
	 * @dataProvider unrestrictedPostCombos
	 */
	public function it_should_return_an_array_of_existing_unrestricted_post_i_ds( $taxonomies, $post_type, $post_count ) {
		$taxonomies_w_default_terms = array_filter( $taxonomies, function ( $terms ) {
			return ! empty( $terms );
		} );

		foreach ( $taxonomies as $tax => $terms ) {
			register_taxonomy( $tax, [ ] );
			register_taxonomy_for_object_type( $tax, $post_type );
			foreach ( $terms as $t ) {
				wp_insert_term( $t, $tax, [ 'slug' => $t ] );
			}
			$user_slug_provider = Test::replace( 'trc_Public_UserSlugProviderInterface' )
			                          ->method( 'get_default_post_terms', $terms )
			                          ->get();
			$this->sut->set_user_slug_provider_for( $tax, $user_slug_provider );
		}

		$this->factory->post->create_many( $post_count, [ 'post_type' => $post_type ] );

		$out = $this->sut->get_unrestricted_posts();

		if ( $post_count ) {
			Test::assertCount( count( $taxonomies_w_default_terms ), $out );
			foreach ( array_keys( $taxonomies_w_default_terms ) as $tax_name ) {
				Test::assertArrayHasKey( $tax_name, $out );
				Test::assertCount( $post_count, $out[ $tax_name ] );
			}
		} else {
			Test::assertEmpty( $out );
		}
	}
}