<?php declare(strict_types=1);

/**
 * Test cases for multiple cache drivers.
 */

namespace PinkCrab\WP_PSR16_Cache\Tests;

use DateInterval;
use InvalidArgumentException;
use PinkCrab\WP_PSR16_Cache\CacheInterface_Trait;

trait Test_Case_Trait {
	/**
	 * @return void
	 */
	public function testCanCreateWithoutExpiry(): void {
		$this->cache->set( 'a', 'Alpha' );
		$this->assertEquals( 'Alpha', $this->cache->get( 'a' ), self::class );
		sleep( 3 );
		$this->assertEquals( 'Alpha', $this->cache->get( 'a' ) );
	}

	/**
	 * @return void
	 */
	public function testCanCreateWithExpiry(): void {
		$this->cache->set( 'b', 'Bravo', 2 );
		$this->assertEquals( 'Bravo', $this->cache->get( 'b' ) );
		sleep( 3 );
		$this->assertEquals( 'Fallback', $this->cache->get( 'b', 'Fallback' ) );

		$this->cache->set( 'c', 'Charlie', 4 );
		$this->assertEquals( 'Charlie', $this->cache->get( 'c' ) );
		sleep( 2 );
		$this->assertEquals( 'Charlie', $this->cache->get( 'c', 'FallbackC' ) );
		sleep( 3 );
		$this->assertEquals( 'FallbackC', $this->cache->get( 'c', 'FallbackC' ) );
	}

	/**
	 * @return void
	 */
	public function testHasKey(): void {
		$this->cache->set( 'd', 'Delta' );
		$this->assertTrue( $this->cache->has( 'd' ) );
		$this->assertFalse( $this->cache->has( 'NONESETKEY' ) );
	}

	/**
	 * @return void
	 */
	public function testCanDeleteKey(): void {
		$this->cache->set( 'e', 'Erm' );
		$this->assertTrue( $this->cache->has( 'e' ) );
		$this->cache->delete( 'e' );
		$this->assertFalse( $this->cache->has( 'e' ) );
	}

	/**
	 * @return void
	 */
	public function testCanClearKeys(): void {

		$this->cache->set( 'f', 'Foxes' );
		$this->cache->set( 'g', 'Ghostbusters' );
		$this->assertTrue( $this->cache->has( 'f' ) );
		$this->assertTrue( $this->cache->has( 'g' ) );

		$this->assertTrue( $this->cache->clear() );

		$this->assertFalse( $this->cache->has( 'f' ) );
		$this->assertFalse( $this->cache->has( 'g' ) );
	}

	/**
	 * @return void
	 */
	public function testCanSetMultiple(): void {
		$this->cache->setMultiple(
			array(
				'a1' => 'A one',
				'b2' => 'B two',
			)
		);
		$this->assertEquals( 'A one', $this->cache->get( 'a1' ) );
		$this->assertEquals( 'B two', $this->cache->get( 'b2' ) );

		$this->cache->clear();
	}

	/**
	 * @return void
	 */
	public function testCanGetMultipleNoDefault(): void {
		$this->cache->setMultiple(
			array(
				'c3' => 'C three',
				'd4' => 'D four',
			)
		);
		$this->cache->set( 'e5', 'E five' );

		$values = $this->cache->getMultiple(
			array( 'c3', 'd4', 'e5', 'f6' )
		);
		$this->assertEquals( 'C three', $values['c3'] );
		$this->assertEquals( 'D four', $values['d4'] );
		$this->assertEquals( 'E five', $values['e5'] );
		$this->assertNull( $values['f6'] );

		$this->cache->clear();
	}

	/**
	 * @return void
	 */
	public function testCanGetMultipleWithDefault(): void {
		$this->cache->setMultiple(
			array(
				'g7' => 'G seven',
				'h8' => 'H eight',
			)
		);
		$values = $this->cache->getMultiple(
			array( 'g7', 'h8', 'i9' ),
			'FALLBACK'
		);
		$this->assertEquals( 'G seven', $values['g7'] );
		$this->assertEquals( 'H eight', $values['h8'] );
		$this->assertEquals( 'FALLBACK', $values['i9'] );

		$this->cache->clear();
	}

	/**
	 * @return void
	 */
	public function testCanDeleteMultiple(): void {
		$this->cache->setMultiple(
			array(
				'i9'  => 'I Nine',
				'j10' => 'J Ten',
			)
		);
		$this->cache->deleteMultiple( array( 'i9', 'j10' ) );
		$this->assertNull( $this->cache->get( 'i9' ) );
		$this->assertNull( $this->cache->get( 'j10' ) );

		$this->cache->clear();
	}

	/**
	 * @return void
	 */
	public function testCanUseDateIntervalAsTTL(): void {
		$inteval = DateInterval::createFromDateString( '2 seconds' );
		$this->cache->set( 'b', 'Bravo', $inteval );
		$this->assertEquals( 'Bravo', $this->cache->get( 'b' ) );
		sleep( 3 );
		$this->assertEquals( 'Fallback', $this->cache->get( 'b', 'Fallback' ) );

		$this->cache->clear();
	}

	/**
	 * @return void
	 */
	public function testThrowsInvalidArgumentExceptionIfBlankKey(): void {
		$this->expectException( InvalidArgumentException::class );
		$this->cache->set( '', 'Blank' );
	}

	 /**
	 * @return void
	 */
	public function testThrowsInvalidArgumentExceptionIfArray(): void {
		$this->expectException( InvalidArgumentException::class );
		$this->cache->get( array( 'invalid', 'key' ), 'array' );
	}

	/**
	 * Test that get returns false if none valid parameter passed.
	 *
	 * @return void
	 */
	public function testGetReturnsDefaultIfInvalidKeyValues(): void {
		$this->assertEquals( 'INVALID', $this->cache->get( '@1', 'INVALID' ) );
	}

	/**
	 * Test that set returns false if none valid parameter passed.
	 *
	 * @return void
	 */
	public function testSetRetrunsFalseIfInvalidKeyValues(): void {
		$this->assertFalse( $this->cache->set( '@1', 'INVALID' ) );
	}

	/**
	 * Test that delete returns false if none valid parameter passed.
	 *
	 * @return void
	 */
	public function testDeleteRetrunsFalseIfInvalidKeyValues(): void {
		$this->assertFalse( $this->cache->delete( '@1' ) );
	}

	/**
	 * Test that set returns false if none valid parameter passed.
	 *
	 * @return void
	 */
	public function testHasRetrunsFalseIfInvalidKeyValues(): void {
		$this->assertFalse( $this->cache->has( '1' ) );
	}

	/**
	 * Test that only valid keys (alpha numeric strings are allowed.)
	 *
	 * @return void
	 */
	public function test_valid_key(): void {
		$cache = new class() {
			use CacheInterface_Trait;
			public function valid_key( $key ): bool {
				return $this->is_valid_key_value( $key );
			}
		};

		$valid = array(
			'3e3dc99bedcd3c30416fed1e803cfc8c',
			'bcc17601257888da30fbad56eb25dc60',
			md5( strval( time() ) ),
			md5( '124564' ),
			md5( strval( 12 + 6767 ) ),
		);

		foreach ( $valid as $key ) {
			$this->assertTrue( $cache->valid_key( $key ) );
		}

		$invalid = array(
			'@at',
			'{open',
			'}close',
			'/me\\',
		);

		foreach ( $invalid as $key ) {
			$this->assertFalse( $cache->valid_key( $key ), "INVALID {$key}" );
		}
	}
}
