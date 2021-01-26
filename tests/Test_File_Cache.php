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

}
