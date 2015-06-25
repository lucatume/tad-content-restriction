<?php


use tad\FunctionMocker\FunctionMocker as Test;

class trc_TemplateRestrictorTest extends \PHPUnit_Framework_TestCase {

	protected function setUp() {
		Test::setUp();
	}

	protected function tearDown() {
		Test::tearDown();
	}

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		Test::assertInstanceOf( 'trc_TemplateRestrictor', trc_TemplateRestrictor::instance() );
	}

	/**
	 * @test
	 * it should not redirect if content restriction is deactivated for template
	 */
	public function it_should_not_redirect_if_content_restriction_is_deactivated_for_template() {
		$sut = new trc_TemplateRestrictor();

		$templates = Test::replace( 'trc_Templates' )->method( 'should_restrict_template', false )->get();
		$sut->set_templates( $templates );

		Test::assertEquals( 'foo', $sut->maybe_redirect( 'foo' ) );
	}

	/**
	 * @test
	 * it should not redirect if the current post type is not a restricted post type
	 */
	public function it_should_not_redirect_if_the_current_post_type_is_not_a_restricted_post_type() {
		$sut = new trc_TemplateRestrictor();

		$templates = Test::replace( 'trc_Templates' )->method( 'should_restrict_template', true )->get();
		$sut->set_templates( $templates );

		Test::replace( 'get_post_type', 'post' );

		$post_types = Test::replace( 'trc_PostTypes' )->method( 'is_restricted_post_type', false )->get();
		$sut->set_post_types( $post_types );

		Test::assertEquals( 'foo', $sut->maybe_redirect( 'foo' ) );
	}

	/**
	 * @test
	 * it should not redirect if user can access template
	 */
	public function it_should_not_redirect_if_user_can_access_template() {
		$sut = new trc_TemplateRestrictor();

		$templates = Test::replace( 'trc_Templates' )->method( 'should_restrict_template', true )->get();
		$sut->set_templates( $templates );

		Test::replace( 'get_post_type', 'post' );

		$post_types = Test::replace( 'trc_PostTypes' )->method( 'is_restricted_post_type', true )->get();
		$sut->set_post_types( $post_types );

		$user = Test::replace( 'trc_User' )->method( 'can_access_template', true )->get();
		$sut->set_user( $user );

		Test::assertEquals( 'foo', $sut->maybe_redirect( 'foo' ) );
	}

	/**
	 * @test
	 * it should redirect if user has no access to template
	 */
	public function it_should_redirect_if_user_has_no_access_to_template() {
		$sut = new trc_TemplateRestrictor();

		$templates = Test::replace( 'trc_Templates' )->method( 'should_restrict_template', true )
		                 ->method( 'get_redirection_template', '403.php' )->get();
		$sut->set_templates( $templates );

		Test::replace( 'get_post_type', 'post' );

		$post_types = Test::replace( 'trc_PostTypes' )->method( 'is_restricted_post_type', true )->get();
		$sut->set_post_types( $post_types );

		$user = Test::replace( 'trc_User' )->method( 'can_access_template', false )->get();
		$sut->set_user( $user );

		Test::assertEquals( '403.php', $sut->maybe_redirect( 'foo' ) );
	}

}