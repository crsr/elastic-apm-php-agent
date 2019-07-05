<?php
namespace HT\Tests;

use HT\Agent;
use HT\Stores\ErrorsStore;
use HT\Stores\TransactionsStore;
use HT\Transaction\Summary;
use Yaoi\Mock;

/**
 * Test Case for @see \HT\Agent
 */
final class AgentTest extends TestCase {

  /**
   * @covers \HT\Agent::__construct
   * @covers \HT\Agent::startTransaction
   * @covers \HT\Agent::stopTransaction
   * @covers \HT\Agent::getTransaction
   */
  public function testStartAndStopATransaction() {
    $agent = new Agent(array('appName' => 'phpunit_1'));

    // Create a Transaction, wait and Stop it
    $name = 'trx';
    $agent->startTransaction( $name );
    usleep( 10 * 1000 ); // sleep milliseconds
    $agent->stopTransaction( $name );

    // Transaction Summary must be populated
    $summary = $agent->getTransaction( $name )->getSummary();

    $this->assertArrayHasKey( 'duration', $summary );
    $this->assertArrayHasKey( 'backtrace', $summary );

    // Expect duration in milliseconds
    $this->assertDurationIsWithinThreshold(10, $summary['duration']);
    $this->assertNotEmpty( $summary['backtrace'] );
  }

  /**
   * @covers \HT\Agent::__construct
   * @covers \HT\Agent::startTransaction
   * @covers \HT\Agent::stopTransaction
   * @covers \HT\Agent::getTransaction
   */
  public function testStartAndStopATransactionWithExplicitStart() {
    $agent = new Agent(array('appName' => 'phpunit_1'));

    // Create a Transaction, wait and Stop it
    $name = 'trx';
    $agent->startTransaction( $name, array(), microtime(true) - 1);
    usleep( 500 * 1000 ); // sleep milliseconds
    $agent->stopTransaction( $name );

    // Transaction Summary must be populated
    $summary = $agent->getTransaction( $name )->getSummary();

    $this->assertArrayHasKey( 'duration', $summary );
    $this->assertArrayHasKey( 'backtrace', $summary );

    // Expect duration in milliseconds
    $this->assertDurationIsWithinThreshold(1500, $summary['duration'], 150);
    $this->assertNotEmpty( $summary['backtrace'] );
  }
}
