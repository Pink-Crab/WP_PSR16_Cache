<?php

declare(strict_types=1);


/**
 * The WordPress transient driver for the PinkCrab Peristant Cache interface.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author Glynn Quelch <glynn.quelch@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @package PinkCrab\WP_PSR16_Cache
 */

namespace PinkCrab\WP_PSR16_Cache;

use stdClass;
use DateInterval;
use InvalidArgumentException;
use Psr\SimpleCache\CacheInterface;
use PinkCrab\WP_PSR16_Cache\CacheInterface_Trait;

class Transient_Cache implements CacheInterface {

	/**
	 * @uses CacheInterface_Trait::is_valid_key_value()
	 * @uses CacheInterface_Trait::ttl_to_seconds()
	 * @uses CacheInterface_Trait::all_true()
	 */
	use CacheInterface_Trait;

	/**
	 * Postfix to wrap any group key.
	 */
	const CACHE_KEY_POSTFIX = '_';

	/**
	 * The group used for the keys being set/get
	 *
	 * @var string|null
	 */
	protected $group = '';

	/**
	 * Creates an instance of the Transient Cache.
	 *
	 * @param string|null $group Optional namespace prefixed onto every transient key (null disables prefixing).
	 */
	public function __construct( ?string $group = null ) {
		$this->group = $group;
	}

	/**
	 * Sets a key.
	 * Used to conform with Psr\Simple-Cache
	 *
	 * @param string                 $key   The key of the item to store.
	 * @param mixed                  $value The value of the item to store, must be serializable.
	 * @param null|int|\DateInterval $ttl   Lifetime for the entry; null or 0 persists with no expiry.
	 * @return bool                         True if the transient was written, false on invalid key or write failure.
	 * @throws InvalidArgumentException     When the key is not a non-empty string.
	 */
	public function set( $key, $value, $ttl = null ) {
		if ( ! $this->is_valid_key_value( $key ) ) {
			return false;
		}
		return \set_transient(
			$this->parse_key( $key ),
			$value,
			$this->ttl_to_seconds( $ttl )
		);
	}

	/**
	 * Attempts to get from cache, return defualt if nothing returned.
	 *
	 * @param string $key           Cache key to look up (prefixed with the group if one is set).
	 * @param mixed  $default_value Fallback returned when the transient is missing or falsy.
	 * @return mixed                The stored value, or $default_value on miss/invalid key.
	 * @throws InvalidArgumentException When the key is not a non-empty string.
	 */
	public function get( $key, $default_value = null ) {
		if ( ! $this->is_valid_key_value( $key ) ) {
			return $default_value;
		}
		$value = \get_transient( $this->parse_key( $key ) );
		return $value ? $value : $default_value;
	}

	/**
	 * Clears a defined cached instance.
	 *
	 * @param string $key Cache key of the transient to remove.
	 * @return bool       True if the transient was deleted, false on invalid key or if it did not exist.
	 * @throws InvalidArgumentException When the key is not a non-empty string.
	 */
	public function delete( $key ) {
		if ( ! $this->is_valid_key_value( $key ) ) {
			return false;
		}
		return \delete_transient( $this->parse_key( $key ) );
	}

	/**
	 * Clears all transients for the defined group.
	 *
	 * @return bool True only if every matching transient was deleted successfully.
	 */
	public function clear() {
		$results = array_map(
			function ( $e ) {
				return \delete_transient( $this->parse_key( $e ) );
			},
			$this->get_group_keys()
		);
		return ! \in_array( false, $results, true );
	}

	/**
	 * Gets multiple cache values based on an array of keys.
	 *
	 * @param array<int, string> $keys          Cache keys to fetch.
	 * @param mixed              $default_value Fallback returned for any key that is not in cache.
	 * @return array<string, mixed>             Map of key => cached value (or default where missing).
	 */
	public function getMultiple( $keys, $default_value = null ) {
		return array_reduce(
			$keys,
			function ( $carry, $key ) use ( $default_value ) {
				$carry[ $key ] = $this->get( $key, $default_value );
				return $carry;
			},
			array()
		);
	}

	/**
	 * Sets multiple keys based ona  key => value array.
	 *
	 * @param array<string, mixed>  $values Map of key => value to write in one batch.
	 * @param DateInterval|int|null $ttl    Shared lifetime applied to every entry; null or 0 persists with no expiry.
	 * @return bool                         True only if every individual set() call succeeded.
	 */
	public function setMultiple( $values, $ttl = null ) {
		return $this->all_true(
			array_reduce(
				array_keys( $values ),
				function ( $carry, $key ) use ( $values, $ttl ) {
					$carry[ $key ] = $this->set( $key, $values[ $key ], $ttl );
					return $carry;
				},
				array()
			)
		);
	}

	/**
	 * Deletes multiple keys based on an arrya of keys.
	 *
	 * @param array<int, string> $keys List of cache keys to remove.
	 * @return bool                    True only if every individual delete() call succeeded.
	 */
	public function deleteMultiple( $keys ) {
		return $this->all_true(
			array_reduce(
				$keys,
				function ( $carry, $key ) {
					$carry[ $key ] = $this->delete( $key );
					return $carry;
				},
				array()
			)
		);
	}

	/**
	 * Checks if a key is defined in transient.
	 *
	 * @param string $key Cache key to probe.
	 * @return bool       True when get() returns a non-null value for the key.
	 */
	public function has( $key ) {
		return ! is_null( $this->get( $key ) );
	}

	/**
	 * Sets the key basd on the group
	 *
	 * @param string $key Raw user-supplied cache key.
	 * @return string     Key with the group prefix applied (unchanged when no group is set).
	 */
	protected function parse_key( string $key ): string {
		return \sprintf(
			'%s%s',
			$this->group_key_prefix(),
			$key
		);
	}

	/**
	 * Sets the defined prefix to keys.
	 *
	 * @return string The group name followed by the postfix separator, or an empty string when no group is set.
	 */
	protected function group_key_prefix(): string {
		return $this->group
			? $this->group . self::CACHE_KEY_POSTFIX
			: '';
	}

	/**
	 * Returns all keys that match for this group.
	 *
	 * @return array<int, string> The base cache keys (group prefix and _transient_ wrapper stripped).
	 */
	protected function get_group_keys(): array {
		// Extract the base cache keys (excluding pre/postfixes)
		return array_map(
			function ( $key ) {
				return \str_replace(
					'_transient_' . $this->group_key_prefix(),
					'',
					(string) $key
				);
			},
			$this->get_transients()
		);
	}

	/**
	 * Gets all transients with a matching
	 *
	 * @uses global $wpdb
	 * @return array<int, string> Raw option_name values from the options table matching this group's transient prefix.
	 */
	protected function get_transients(): array {
		global $wpdb;

		return $wpdb->get_col(
			$wpdb->prepare(
				"SELECT option_name AS name, option_value AS value FROM {$wpdb->options} WHERE option_name LIKE %s",
				'_transient_' . $this->group_key_prefix() . '%'
			)
		);
	}
}
