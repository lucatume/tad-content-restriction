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

}