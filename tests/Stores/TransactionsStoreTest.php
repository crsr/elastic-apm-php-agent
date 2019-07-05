<?php
namespace HT\Tests\Stores;

use HT\Stores\TransactionsStore;
use HT\Events\Transaction;
use HT\Tests\TestCase;

/**
 * Test Case for @see \HT\Stores\TransactionsStore
 */
final class TransactionsStoreTest extends TestCase {

  /**
   * @covers \HT\Stores\TransactionsStore::register
   * @covers \HT\Stores\TransactionsStore::get
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
   * @covers \HT\Stores\TransactionsStore::get
   */
  public function testFetchUnknownTransaction() {
    $store = new TransactionsStore();
    $this->assertNull( $store->fetch( 'unknown' ) );
  }

}
