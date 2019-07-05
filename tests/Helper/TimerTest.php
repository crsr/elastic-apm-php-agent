<?php
namespace HT\Tests\Helper;

use HT\Exception\Timer\AlreadyRunningException;
use HT\Helper\Timer;
use HT\Tests\TestCase;

/**
 * Test Case for @see \HT\Helper\Timer
 */
final class TimerTest extends TestCase {

  /**
   * @covers \HT\Helper\Timer::start
   * @covers \HT\Helper\Timer::stop
   * @covers \HT\Helper\Timer::getDuration
   * @covers \HT\Helper\Timer::toMicro
   */
  public function testCanBeStartedAndStoppedWithDuration() {
    $timer = new Timer();
    $duration = rand( 25, 100 );

    $timer->start();
    usleep( $duration );
    $timer->stop();

    $this->assertGreaterThanOrEqual( $duration, $timer->getDuration() );
  }

    /**
     * @covers \HT\Helper\Timer::start
     * @covers \HT\Helper\Timer::stop
     * @covers \HT\Helper\Timer::getDuration
     * @covers \HT\Helper\Timer::toMicro
     */
    public function testCanCalculateDurationInMilliseconds() {
        $timer = new Timer();
        $duration = rand( 25, 100 ); // duration in milliseconds

        $timer->start();
        usleep( $duration * 1000 ); // sleep microseconds
        $timer->stop();

        $this->assertDurationIsWithinThreshold($duration, $timer->getDurationInMilliseconds());
    }

  /**
   * @depends testCanBeStartedAndStoppedWithDuration
   *
   * @covers \HT\Helper\Timer::start
   * @covers \HT\Helper\Timer::stop
   * @covers \HT\Helper\Timer::getDuration
   * @covers \HT\Helper\Timer::getElapsed
   * @covers \HT\Helper\Timer::toMicro
   */
  public function testGetElapsedDurationWithoutError() {
    $timer = new Timer();

    $timer->start();
    usleep( 10 );
    $elapsed = $timer->getElapsed();
    $timer->stop();

    $this->assertGreaterThanOrEqual( $elapsed, $timer->getDuration() );
    $this->assertEquals( $timer->getElapsed(), $timer->getDuration() );
  }

    /**
     * @covers \HT\Helper\Timer::start
     * @covers \HT\Helper\Timer::getDurationInMilliseconds
     */
    public function testCanBeStartedWithExplicitStartTime() {
        $timer = new Timer(microtime(true) - .5); // Start timer 500 milliseconds ago

        usleep(500 * 1000); // Sleep for 500 milliseconds

        $timer->stop();

        $duration = $timer->getDurationInMilliseconds();

        // Duration should be more than 1000 milliseconds
        //  sum of initial offset and sleep
        $this->assertGreaterThanOrEqual(1000, $duration);
    }
}
