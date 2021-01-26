<?php

declare(strict_types=1);

/**
 * Tests the transient cache driver
 *
 * @since 1.0.0
 * @author GLynn Quelch <glynn.quelch@gmail.com>
 */
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use PinkCrab\WP_PSR16_Cache\File_Cache;
use PinkCrab\WP_PSR16_Cache\Transient_Cache;
use PinkCrab\WP_PSR16_Cache\Tests\Test_Case_Trait;

class Test_File_Cache extends TestCase {

	use Test_Case_Trait;

	/**
	 * The Tranient Cache Implementation
	 *
	 * @var CacheInterface
	 */
	protected $cache;

	public function setUp() {
		$this->cache = new File_Cache( __DIR__ . '/File_Cache_FS' );
	}

	/**             RUNS ALL TESTS FROM TRAIT!             */

	/**
	 * Tests that all transients set with no ttl or an invalid type
	 * are set to 0 (do not expire)
	 *
	 * @return void
	 */
	public function testTransientsWithNoTTLHaveNoExpiry(): void {
		$this->cache->set( 'as_array', 'Zulu', array( 'array', 'should never expire' ) );
		$this->assertEquals( 0, (int) get_option( '_transient_timeout_tests_as_array', 0 ) );

		 $this->cache->set( 'as_zero', 'Zulu', 0 );
		$this->assertEquals( 0, (int) get_option( '_transient_timeout_tests_as_zero', 0 ) );
	}
}
