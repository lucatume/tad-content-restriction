<?php

use tad\FunctionMocker\FunctionMocker as Test;

class trc_Core_QueryMarshalTest extends \PHPUnit_Framework_TestCase {

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
		Test::assertInstanceOf( 'trc_Core_QueryMarshal', new trc_Core_QueryMarshal() );
	}
}
