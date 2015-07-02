<?php

use tad\FunctionMocker\FunctionMocker as Test;

class trc_Core_QueryRestrictorTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		Test::setUp();
	}

	public function tearDown() {
		Test::tearDown();
	}

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		Test::assertInstanceOf( 'trc_Core_QueryRestrictor', new trc_Core_QueryRestrictor() );
	}

	/**
	 * @test
	 * it should not restrict the query if there are no restricting taxonomies
	 */
	public function it_should_not_restrict_the_query_if_there_are_no_restricting_taxonomies() {
		$sut = new trc_Core_QueryRestrictor();

		$taxonomies = Test::replace( 'trc_Core_RestrictingTaxonomies' )
		                  ->method( 'get_restricting_taxonomies_for', [ ] )
		                  ->get();
		$sut->set_taxonomies( $taxonomies );

		Test::assertFalse( $sut->should_restrict_query( $this->get_mock_query() ) );
	}

	private function get_mock_query() {
		return Test::replace( 'WP_Query' )
		           ->get();
	}

	/**
	 * @test
	 * it should not restrict the query if queries are not to be restricted
	 */
	public function it_should_not_restrict_the_query_if_queries_are_not_to_be_restricted() {
		$sut = new trc_Core_QueryRestrictor();

		$taxonomies = Test::replace( 'trc_Core_RestrictingTaxonomies' )
		                  ->method( 'get_restricting_taxonomies_for', [ 'tax_a' ] )
		                  ->get();
		$sut->set_taxonomies( $taxonomies );

		$queries = Test::replace( 'trc_Core_Queries' )
		               ->method( 'should_restrict_queries', false )
		               ->get();
		$sut->set_queries( $queries );

		Test::assertFalse( $sut->should_restrict_query( $this->get_mock_query() ) );
	}

	/**
	 * @test
	 * it should not restrict the query if the query is not to be restricted
	 */
	public function it_should_not_restrict_the_query_if_the_query_is_not_to_be_restricted() {
		$sut = new trc_Core_QueryRestrictor();

		$taxonomies = Test::replace( 'trc_Core_RestrictingTaxonomies' )
		                  ->method( 'get_restricting_taxonomies_for', [ 'tax_a' ] )
		                  ->get();
		$sut->set_taxonomies( $taxonomies );

		$queries = Test::replace( 'trc_Core_Queries' )
		               ->method( 'should_restrict_queries', true )
		               ->method( 'should_restrict_query', false )
		               ->get();
		$sut->set_queries( $queries );

		Test::assertFalse( $sut->should_restrict_query( $this->get_mock_query() ) );
	}

	/**
	 * @test
	 * it should not restrict the query if the post type is not a restricted post type
	 */
	public function it_should_not_restrict_the_query_if_the_post_type_is_not_a_restricted_post_type() {
		$sut = new trc_Core_QueryRestrictor();

		$taxonomies = Test::replace( 'trc_Core_RestrictingTaxonomies' )
		                  ->method( 'get_restricting_taxonomies_for', [ 'tax_a' ] )
		                  ->get();
		$sut->set_taxonomies( $taxonomies );

		$queries = Test::replace( 'trc_Core_Queries' )
		               ->method( 'should_restrict_queries', true )
		               ->method( 'should_restrict_query', true )
		               ->get();
		$sut->set_queries( $queries );

		$post_types = Test::replace( 'trc_Core_PostTypes' )
		                  ->method( 'is_restricted_post_type', false )
		                  ->get();
		$sut->set_post_types( $post_types );

		Test::assertFalse( $sut->should_restrict_query( $this->get_mock_query() ) );
	}

	/**
	 * @test
	 * it should add a restricting tax query if one restricting taxonomy is present
	 */
	public function it_should_add_a_restricting_tax_query_if_one_restricting_taxonomy_is_present() {
		$sut = new trc_Core_QueryRestrictor();

		$taxonomies = Test::replace( 'trc_Core_RestrictingTaxonomies' )
		                  ->method( 'get_restricting_taxonomies_for', [ 'tax_a' ] )
		                  ->get();
		$sut->set_taxonomies( $taxonomies );

		$post_types = Test::replace( 'trc_Core_PostTypesInterface' )
		                  ->method( 'get_restricted_post_types', [ 'post_type' ] )
		                  ->method( 'is_restricted_post_type', true )
		                  ->method( 'get_restricted_post_types_in', [ 'post_type' ] )
		                  ->get();
		$sut->set_post_types( $post_types );

		$filtering_taxonomy = Test::replace( 'trc_Core_FilteringTaxQueryGenerator' )
		                          ->method( 'get_tax_query_for', 'restricting_tax_query' )
		                          ->get();
		$sut->set_filtering_taxonomy_generator( $filtering_taxonomy );

		$query                     = Test::replace( 'WP_Query' )
		                                 ->method( 'get', [ 'post_type' ] )
		                                 ->get();
		$query->tax_query          = new stdClass();
		$query->tax_query->queries = [ ];

		$this->replace_query_manager( false, [ ] );

		$sut->restrict_query( $query );

		Test::assertEquals( array( 'restricting_tax_query' ), $query->tax_query->queries );
	}

	/**
	 * @test
	 * it should add a restricting tax query for each restricting taxonomy
	 */
	public function it_should_add_a_restricting_tax_query_for_each_restricting_taxonomy() {
		$sut = new trc_Core_QueryRestrictor();

		$taxonomies = Test::replace( 'trc_Core_RestrictingTaxonomies' )
		                  ->method( 'get_restricting_taxonomies_for', [ 'tax_a', 'tax_b' ] )
		                  ->get();
		$sut->set_taxonomies( $taxonomies );

		$post_types = Test::replace( 'trc_Core_PostTypesInterface' )
		                  ->method( 'get_restricted_post_types', [ 'post_type' ] )
		                  ->method( 'is_restricted_post_type', true )
		                  ->method( 'get_restricted_post_types_in', [ 'post_type' ] )
		                  ->get();
		$sut->set_post_types( $post_types );

		$filtering_taxonomy = Test::replace( 'trc_Core_FilteringTaxQueryGenerator' )
		                          ->method( 'get_tax_query_for', 'restricting_tax_query' )
		                          ->get();
		$sut->set_filtering_taxonomy_generator( $filtering_taxonomy );

		$query                     = Test::replace( 'WP_Query' )
		                                 ->method( 'get', [ 'post_type' ] )
		                                 ->get();
		$query->tax_query          = new stdClass();
		$query->tax_query->queries = [ ];

		$this->replace_query_manager( false, [ ] );

		$sut->restrict_query( $query );

		Test::assertEquals( array( 'restricting_tax_query', 'restricting_tax_query' ), $query->tax_query->queries );
	}

	/**
	 * @test
	 * it should leave previous tax queries in place when adding restricting tax queries
	 */
	public function it_should_leave_previous_tax_queries_in_place_when_adding_restricting_tax_queries() {
		$sut = new trc_Core_QueryRestrictor();

		$taxonomies = Test::replace( 'trc_Core_RestrictingTaxonomies' )
		                  ->method( 'get_restricting_taxonomies_for', [ 'tax_a', 'tax_b' ] )
		                  ->get();
		$sut->set_taxonomies( $taxonomies );

		$post_types = Test::replace( 'trc_Core_PostTypesInterface' )
		                  ->method( 'get_restricted_post_types', [ 'post_type' ] )
		                  ->method( 'is_restricted_post_type', true )
		                  ->method( 'get_restricted_post_types_in', [ 'post_type' ] )
		                  ->get();
		$sut->set_post_types( $post_types );

		$filtering_taxonomy = Test::replace( 'trc_Core_FilteringTaxQueryGenerator' )
		                          ->method( 'get_tax_query_for', 'restricting_tax_query' )
		                          ->get();
		$sut->set_filtering_taxonomy_generator( $filtering_taxonomy );

		$query                     = Test::replace( 'WP_Query' )
		                                 ->method( 'get', [ 'post_type' ] )
		                                 ->get();
		$query->tax_query          = new stdClass();
		$query->tax_query->queries = [ 'here before' ];

		$this->replace_query_manager( false, [ ] );

		$sut->restrict_query( $query );

		Test::assertEquals( array(
			'here before',
			'restricting_tax_query',
			'restricting_tax_query'
		), $query->tax_query->queries );
	}

	/**
	 * @test
	 * it should not add a tax query if restricting a multi post type query where some post types are not restricted
	 */
	public function it_should_not_add_a_tax_query_if_restricting_a_multi_post_type_query_where_some_post_types_are_not_restricted() {
		$sut = new trc_Core_QueryRestrictor();

		$taxonomies = Test::replace( 'trc_Core_RestrictingTaxonomies' )
		                  ->method( 'get_restricting_taxonomies_for', [ 'tax_a' ] )
		                  ->get();
		$sut->set_taxonomies( $taxonomies );

		$filtering_taxonomy_generator = Test::replace( 'trc_Core_FilteringTaxQueryGeneratorInterface' )
		                                    ->get();
		$sut->set_filtering_taxonomy_generator( $filtering_taxonomy_generator );

		$post_types = Test::replace( 'trc_Core_PostTypesInterface' )
		                  ->method( 'get_restricted_post_types', [ 'post' ] )
		                  ->method( 'is_restricted_post_type', true )
		                  ->method( 'get_restricted_post_types_in', [ 'post' ] )
		                  ->get();
		$sut->set_post_types( $post_types );

		$query                     = Test::replace( 'WP_Query' )
		                                 ->method( 'get', [ 'post', 'page' ] )
		                                 ->get();
		$query->tax_query          = new stdClass();
		$query->tax_query->queries = [ ];

		$trc_query = Test::replace( 'trc_Core_FastIDQuery' )
		                 ->method( 'get_posts', [ ] )
		                 ->get();
		Test::replace( 'trc_Core_FastIDQuery::instance', $trc_query );

		$this->replace_query_manager( true, [ ] );

		$sut->restrict_query( $query );

		$filtering_taxonomy_generator->wasNotCalled( 'get_tax_query_for' );
	}

	/**
	 * @test
	 * it should add excluded post ids to query when querying for restricted and unrestricted post types
	 */
	public function it_should_add_excluded_post_ids_to_query_when_querying_for_restricted_and_unrestricted_post_types() {
		$sut = new trc_Core_QueryRestrictor();

		$taxonomies = Test::replace( 'trc_Core_RestrictingTaxonomies' )
		                  ->method( 'get_restricting_taxonomies_for', [ 'tax_a' ] )
		                  ->get();
		$sut->set_taxonomies( $taxonomies );

		$filtering_taxonomy = Test::replace( 'trc_Core_FilteringTaxQueryGenerator' )
		                          ->method( 'get_tax_query_for', 'restricting_tax_query' )
		                          ->get();
		$sut->set_filtering_taxonomy_generator( $filtering_taxonomy );

		$post_types = Test::replace( 'trc_Core_PostTypesInterface' )
		                  ->method( 'get_restricted_post_types', [ 'post' ] )
		                  ->method( 'is_restricted_post_type', true )
		                  ->method( 'get_restricted_post_types_in', [ 'post' ] )
		                  ->get();
		$sut->set_post_types( $post_types );

		$excluded             = [ '1', '2', '3' ];
		$_query               = new stdClass();
		$_query->post_type    = [ 'post', 'page' ];
		$_query->post__in = [ ];
		$query                = Test::replace( 'WP_Query' )
		                            ->method( 'get_posts', $excluded )
		                            ->method( 'set', function ( $key, $value ) use ( $_query ) {
			                            $_query->{$key} = $value;
		                            } )
		                            ->method( 'get', function ( $key, $default ) use ( $_query ) {
			                            return $_query->{$key} ?: $default;
		                            } )
		                            ->get();
		$query->post_types    = [ 'post', 'page' ];

		$trc_query = Test::replace( 'trc_Core_FastIDQuery' )
		                 ->method( 'get_posts', $excluded )
		                 ->get();
		Test::replace( 'trc_Core_FastIDQuery::instance', $trc_query );

		$this->replace_query_manager( true, [
			'post__in' => [
				Test::replace( 'WP_Query::get_posts', [ 1, 2, 3 ] )
			]
		] );

		$sut->restrict_query( $query );

		Test::assertEquals( $excluded, $_query->post__in );
	}

	/**
	 * @param $has_auxiliary_queries
	 * @param $auxiliary_queries
	 */
	protected function replace_query_manager( $has_auxiliary_queries, $auxiliary_queries ) {
		$query_manager = Test::replace( 'trc_Core_QueryManager' )
		                     ->method( 'has_auxiliary_queries', $has_auxiliary_queries )
		                     ->method( 'get_auxiliary_queries', $auxiliary_queries )
		                     ->get();
		Test::replace( 'trc_Core_QueryManager::instance', $query_manager );
	}
}