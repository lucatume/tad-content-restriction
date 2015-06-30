<?php

use tad\FunctionMocker\FunctionMocker as Test;

class trc_Core_TemplatesTest extends \PHPUnit_Framework_TestCase {

	protected function setUp() {
		Test::setUp();

		Test::replace( 'apply_filters', function ( $tag, $val ) {
			return $val;
		} );
	}

	protected function tearDown() {
		Test::tearDown();
	}

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		Test::assertInstanceOf( 'trc_Core_Templates', new trc_Core_Templates() );
	}

	/**
	 * @test
	 * it should define a default redirection templates list
	 */
	public function it_should_return_a_default_redirection_templates_list() {
		$sut = new trc_Core_Templates();

		$list = [ '403.php', '404.php', 'index.php' ];

		$apply_filters = Test::replace( 'apply_filters', function ( $tag, $val ) {
			return $val;
		} );

		Test::replace( 'locate_template' );

		$sut->get_redirection_template();

		$apply_filters->wasCalledWithOnce( [ 'trc_template_list', $list ] );
	}

	/**
	 * @test
	 * it should allow filtering the redirection template list
	 */
	public function it_should_allow_filtering_the_redirection_template_list() {
		$sut = new trc_Core_Templates();

		Test::replace( 'apply_filters', function ( $tag, $val ) {
			return [ 'foo.php' ];
		} );

		Test::replace( 'locate_template', function ( array $list ) {
			return $list[0];
		} );

		Test::assertEquals( 'foo.php', $sut->get_redirection_template() );
	}

	/**
	 * @test
	 * it should not restrict not singular templates
	 */
	public function it_should_not_restrict_not_singular_templates() {
		Test::replace( 'is_singular', false );

		$sut = new trc_Core_Templates();

		Test::assertFalse( $sut->should_restrict_template( 'foo.php' ) );
	}

	/**
	 * @test
	 * it should not restrict the template if it is an unrestricted one
	 */
	public function it_should_not_restrict_the_template_if_it_is_an_unrestricted_one() {
		Test::replace( 'is_singular', true );
		Test::replace( 'apply_filters', function ( $tag, $val ) {
			return $tag == 'trc_unrestricted_templates' ? [ 'foo.php', 'bar.php' ] : $val;
		} );

		$sut = new trc_Core_Templates();

		Test::assertFalse( $sut->should_restrict_template( 'foo.php' ) );
		Test::assertFalse( $sut->should_restrict_template( 'bar.php' ) );
	}

	/**
	 * @test
	 * it should restrict singular template that's not unrestricted
	 */
	public function it_should_restrict_singular_template_that_s_not_unrestricted() {
		Test::replace( 'is_singular', true );
		Test::replace( 'apply_filters', function ( $tag, $val ) {
			return $tag == 'trc_unrestricted_templates' ? [ 'foo.php', 'bar.php' ] : $val;
		} );

		$sut = new trc_Core_Templates();

		Test::assertTrue( $sut->should_restrict_template( 'woo.php' ) );
	}

	/**
	 * @test
	 * it should allow filtering whether a template should be restricted or not
	 */
	public function it_should_allow_filtering_whether_a_template_should_be_restricted_or_not() {
		Test::replace( 'is_singular', true );
		Test::replace( 'apply_filters', function ( $tag, $val ) {
			$map = [
				'trc_unrestricted_templates'   => [ 'foo.php', 'bar.php' ],
				'trc_should_restrict_template' => false
			];

			return isset( $map[ $tag ] ) ? $map[ $tag ] : $val;
		} );

		$sut = new trc_Core_Templates();

		Test::assertFalse( $sut->should_restrict_template( 'woo.php' ) );
	}
}
