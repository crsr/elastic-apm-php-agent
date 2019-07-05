<?php
namespace HT\Tests\Stores;

use HT\Stores\ErrorsStore;
use HT\Events\Error;
use HT\Tests\TestCase;

/**
 * Test Case for @see \HT\Stores\ErrorsStore
 */
final class ErrorsStoreTest extends TestCase {

  /**
   * @covers \HT\Stores\ErrorsStoreTest::register
   * @covers \HT\Stores\ErrorsStoreTest::list
   */
  public function testCaptureErrorExceptionAndListIt() {
    $store = new ErrorsStore();
    $error = new Error( new \Exception( 'unit-test' ), array() );

    // Must be Empty
    $this->assertTrue( $store->isEmpty() );

    // Store the Error and Check that it's "stored"
    $store->register( $error );
    $list = $store->listing();

    $this->assertEquals( count( $list ), 1 );

    // Must not be Empty
    $this->assertFalse( $store->isEmpty() );
  }

}
