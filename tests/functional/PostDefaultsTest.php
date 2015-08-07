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
}