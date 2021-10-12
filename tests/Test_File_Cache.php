<?php

declare(strict_types=1);

/**
 * Tests the transient cache driver
 *
 * @since 1.0.0
 * @author GLynn Quelch <glynn.quelch@gmail.com>
 */

use PHPUnit\Framework\TestCase;
use Gin0115\WPUnit_Helpers\Objects;
use Psr\SimpleCache\CacheInterface;
use PinkCrab\WP_PSR16_Cache\Cache_Item;
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

	public function test_validate_check_all_item_properties(): void {

		// Mock item
		$item = new Cache_Item( 'key', array( 'test' => 'foo' ), time() );

		// Check returns false if keys do not match.
		$this->assertFalse(
			Objects::invoke_method(
				$this->cache,
				'validate_contents',
				array(
					'not_the_correct_key',
					$item,
				)
			)
		);

		// Function test returns false if expiry is no numerical.
		$item->expiry = 'IM NOT A NUMBER!';
		$this->assertFalse(
			Objects::invoke_method(
				$this->cache,
				'validate_contents',
				array(
					'key',
					$item,
				)
			)
		);
	}

}
