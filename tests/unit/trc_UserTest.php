<?php

use tad\FunctionMocker\FunctionMocker as Test;

class trc_UserTest extends \PHPUnit_Framework_TestCase {

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
		Test::assertInstanceOf( 'trc_User', new trc_User() );
	}

	/**
	 * @test
	 * it should return false if the post is not a valid post
	 */
	public function it_should_return_false_if_the_post_is_not_a_valid_post() {
		Test::replace( 'get_post', null );

		$sut = new trc_User();

		Test::assertFalse( $sut->can_access_post() );
	}
}