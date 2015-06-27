<?php


use tad\FunctionMocker\FunctionMocker as Test;

class trc_TemplateRestrictorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		Test::assertInstanceOf( 'trc_TemplateRedirector', trc_TemplateRedirector::instance() );
	}

	/**
	 * @test
	 * it should not redirect if there are no restricting taxonomies
	 */
	public function it_should_not_restrict_the_query_if_there_are_no_restricting_taxonomies() {
		$sut = new trc_TemplateRedirector();

		$taxonomies = Test::replace( 'trc_taxonomies' )->method( 'get_restricting_taxonomies', [ ] )->get();
		$sut->set_taxonomies( $taxonomies );

		Test::assertEquals( 'foo', $sut->maybe_redirect( 'foo' ) );
	}

	/**
	 * @test
	 * it should not redirect if content restriction is deactivated for template
	 */
	public function it_should_not_redirect_if_content_restriction_is_deactivated_for_template() {
		$sut = new trc_TemplateRedirector();

		$taxonomies = Test::replace( 'trc_taxonomies' )->method( 'get_restricting_taxonomies', [ 'tax_a' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		$templates = Test::replace( 'trc_Templates' )->method( 'should_restrict_template', false )->get();
		$sut->set_templates( $templates );

		Test::assertEquals( 'foo', $sut->maybe_redirect( 'foo' ) );
	}

	/**
	 * @test
	 * it should not redirect if the current post type is not a restricted post type
	 */
	public function it_should_not_redirect_if_the_current_post_type_is_not_a_restricted_post_type() {
		$sut = new trc_TemplateRedirector();

		$taxonomies = Test::replace( 'trc_taxonomies' )->method( 'get_restricting_taxonomies', [ 'tax_a' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		$templates = Test::replace( 'trc_Templates' )->method( 'should_restrict_template', true )->get();
		$sut->set_templates( $templates );

		Test::replace( 'get_post_type', 'post' );

		$post_types = Test::replace( 'trc_PostTypes' )->method( 'is_restricted_post_type', false )->get();
		$sut->set_post_types( $post_types );

		Test::assertEquals( 'foo', $sut->maybe_redirect( 'foo' ) );
	}

	/**
	 * @test
	 * it should not redirect if user can access post
	 */
	public function it_should_not_redirect_if_user_can_access_post() {
		$sut = new trc_TemplateRedirector();

		$taxonomies = Test::replace( 'trc_taxonomies' )->method( 'get_restricting_taxonomies', [ 'tax_a' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		$templates = Test::replace( 'trc_Templates' )->method( 'should_restrict_template', true )->get();
		$sut->set_templates( $templates );

		Test::replace( 'get_post_type', 'post' );

		$post_types = Test::replace( 'trc_PostTypes' )->method( 'is_restricted_post_type', true )->get();
		$sut->set_post_types( $post_types );

		$user = Test::replace( 'trc_UserInterface' )->method( 'can_access_post', true )->get();
		$sut->set_user( $user );

		Test::assertEquals( 'foo', $sut->maybe_redirect( 'foo' ) );
	}

	/**
	 * @test
	 * it should redirect if user has no access to post
	 */
	public function it_should_redirect_if_user_has_no_access_to_post() {
		$sut = new trc_TemplateRedirector();

		$taxonomies = Test::replace( 'trc_taxonomies' )->method( 'get_restricting_taxonomies', [ 'tax_a' ] )->get();
		$sut->set_taxonomies( $taxonomies );

		$templates = Test::replace( 'trc_Templates' )->method( 'should_restrict_template', true )
		                 ->method( 'get_redirection_template', '403.php' )->get();
		$sut->set_templates( $templates );

		Test::replace( 'get_post_type', 'post' );

		$post_types = Test::replace( 'trc_PostTypes' )->method( 'is_restricted_post_type', true )->get();
		$sut->set_post_types( $post_types );

		$user = Test::replace( 'trc_UserInterface' )->method( 'can_access_post', false )->get();
		$sut->set_user( $user );

		Test::assertEquals( '403.php', $sut->maybe_redirect( 'foo' ) );
	}

	protected function setUp() {
		Test::setUp();
	}

	protected function tearDown() {
		Test::tearDown();
	}

}