<?php
namespace PhilKra\Tests\Stores;

use \PhilKra\Stores\TransactionsStore;
use \PhilKra\Events\Transaction;
use PhilKra\Tests\TestCase;

/**
 * Test Case for @see \PhilKra\Stores\TransactionsStore
 */
final class TransactionsStoreTest extends TestCase {

  /**
   * @covers \PhilKra\Stores\TransactionsStore::register
   * @covers \PhilKra\Stores\TransactionsStore::get
   */
  public function testTransactionRegistrationAndFetch() {
    $store = new TransactionsStore();
    $name  = 'test';
    $trx   = new Transaction( $name, array() );

    // Must be Empty
    $this->assertTrue( $store->isEmpty() );

    // Store the Transaction and fetch it then
    $store->register( $trx );
    $proof = $store->fetch( $name );

    // We should get the Same!
    $this->assertEquals( $trx, $proof );
    $this->assertNotNull( $proof );

    // Must not be Empty
    $this->assertFalse( $store->isEmpty() );
  }

  /**
   * @depends testTransactionRegistrationAndFetch
   *
   * @covers \PhilKra\Stores\TransactionsStore::get
   */
  public function testFetchUnknownTransaction() {
    $store = new TransactionsStore();
    $this->assertNull( $store->fetch( 'unknown' ) );
  }

}
