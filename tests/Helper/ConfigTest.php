<?php
namespace HT\Tests\Helper;

use HT\Agent;
use HT\Tests\TestCase;

/**
 * Test Case for @see \HT\Helper\Config
 */
final class ConfigTest extends TestCase {

  /**
   * @covers \HT\Helper\Config::__construct
   * @covers \HT\Agent::getConfig
   * @covers \HT\Helper\Config::getDefaultConfig
   * @covers \HT\Helper\Config::asArray
   */
  public function testControlDefaultConfig() {
    $appName = sprintf( 'app_name_%d', rand( 10, 99 ) );
    $agent = new Agent( array('appName' => $appName));

    // Control Default Config
    $config = $agent->getConfig()->asArray();

    $this->assertArrayHasKey( 'appName', $config );
    $this->assertArrayHasKey( 'secretToken', $config );
    $this->assertArrayHasKey( 'serverUrl', $config );
    $this->assertArrayHasKey( 'hostname', $config );
    $this->assertArrayHasKey( 'active', $config );
    $this->assertArrayHasKey( 'timeout', $config );
    $this->assertArrayHasKey( 'apmVersion', $config );
    $this->assertArrayHasKey( 'appVersion', $config );
    $this->assertArrayHasKey( 'env', $config );
    $this->assertArrayHasKey( 'cookies', $config );
    $this->assertArrayHasKey( 'httpClient', $config );
    $this->assertArrayHasKey( 'environment', $config );
    $this->assertArrayHasKey( 'backtraceLimit', $config );

    $this->assertEquals( $config['appName'], $appName );
    $this->assertNull( $config['secretToken'] );
    $this->assertEquals( $config['serverUrl'], 'http://127.0.0.1:8200' );
    $this->assertEquals( $config['hostname'], gethostname() );
    $this->assertTrue( $config['active'] );
    $this->assertEquals( $config['timeout'], 5 );
    $this->assertEquals( $config['apmVersion'], 'v1' );
    $this->assertEquals( $config['env'], array() );
    $this->assertEquals( $config['cookies'], array() );
    $this->assertEquals( $config['httpClient'], array() );
    $this->assertEquals( $config['environment'], 'development' );
    $this->assertEquals( $config['backtraceLimit'], 0 );
  }

  /**
   * @depends testControlDefaultConfig
   *
   * @covers \HT\Helper\Config::__construct
   * @covers \HT\Agent::getConfig
   * @covers \HT\Helper\Config::getDefaultConfig
   * @covers \HT\Helper\Config::asArray
   */
  public function testControlInjectedConfig() {
    $init = array(
      'appName'       => sprintf( 'app_name_%d', rand( 10, 99 ) ),
      'secretToken'   => hash( 'tiger128,3', time() ),
      'serverUrl'     => sprintf( 'https://node%d.domain.tld:%d', rand( 10, 99 ), rand( 1000, 9999 ) ),
      'appVersion'    => sprintf( '%d.%d.42', rand( 0, 3 ), rand( 0, 10 ) ),
      'frameworkName' => uniqid(),
      'timeout'       => rand( 10, 20 ),
      'hostname'      => sprintf( 'host_%d', rand( 0, 9 ) ),
      'active'        => false,
    );

    $agent = new Agent( $init );

    // Control Default Config
    $config = $agent->getConfig()->asArray();
    foreach( $init as $key => $value ) {
        $this->assertEquals( $config[$key], $init[$key], 'key: ' . $key );
    }
  }

  /**
   * @depends testControlInjectedConfig
   *
   * @covers \HT\Helper\Config::__construct
   * @covers \HT\Agent::getConfig
   * @covers \HT\Helper\Config::getDefaultConfig
   * @covers \HT\Helper\Config::get
   */
  public function testGetConfig() {
    $init = array(
      'appName' => sprintf( 'app_name_%d', rand( 10, 99 ) ),
    );

    $agent = new Agent( $init );
    $this->assertEquals( $agent->getConfig()->get( 'appName' ), $init['appName'] );
  }

}
