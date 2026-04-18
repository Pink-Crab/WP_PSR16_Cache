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
use WP_Filesystem_Direct;
use InvalidArgumentException;
use Psr\SimpleCache\CacheInterface;
use PinkCrab\WP_PSR16_Cache\CacheInterface_Trait;

class File_Cache implements CacheInterface {

	/**
	 * @uses CacheInterface_Trait::is_valid_key_value()
	 * @uses CacheInterface_Trait::ttl_to_seconds()
	 * @uses CacheInterface_Trait::all_true()
	 */
	use CacheInterface_Trait;

	/**
	 * WP_File_System instance.
	 *
	 * @var WP_Filesystem_Direct
	 */
	protected $wp_filesystem;

	/**
	 * Path to cache location.
	 *
	 * @var string
	 */
	protected $filepath;

	/**
	 * Extension for the cache files.
	 *
	 * @var string
	 */
	protected $extension;

	/**
	 * Creates an instance of the FileCache, populated with WP_Filesystem_Direct
	 *
	 * @param string $filepath  Directory path where cache files are written (trailing slashes are stripped).
	 * @param string $extension File extension used for each cache file, including the leading dot.
	 */
	public function __construct( string $filepath, string $extension = '.do' ) {
		$this->filepath  = rtrim( $filepath, '\\/' );
		$this->extension = $extension;

		$this->set_wp_file_system();
		$this->maybe_create_cache_dir();
	}

	/**
	 * Creates an instance of the Direct WP Filesytem.
	 *
	 * @return void Assigns the global $wp_filesystem onto this instance; nothing returned.
	 */
	protected function set_wp_file_system(): void {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;
		$this->wp_filesystem = $wp_filesystem;
	}

	/**
	 * Creates cache directory if it doesnt exist.
	 *
	 * @return void Creates the configured cache directory when absent; nothing returned.
	 */
	protected function maybe_create_cache_dir(): void {
		if ( ! $this->wp_filesystem->exists( $this->filepath ) ) {
			$this->wp_filesystem->mkdir( $this->filepath );
		}
	}

	/**
	 * Checks if key is set.
	 *
	 * @param string $key Cache key to probe.
	 * @return bool       True when a valid, unexpired cache file exists for the key.
	 * @throws InvalidArgumentException When the key is not a non-empty string.
	 */
	public function has( $key ) {
		if ( ! $this->is_valid_key_value( $key ) ) {
			return false;
		}
		return ! is_null( $this->get( $key ) );
	}

	/**
	 * Sets a key.
	 * Used to conform with Psr\Simple-Cache
	 *
	 * @param string                 $key   The key of the item to store.
	 * @param mixed                  $value The value of the item to store, must be serializable.
	 * @param null|int|\DateInterval $ttl   Lifetime for the entry; null or 0 persists with no expiry.
	 * @return bool                         True if the cache file was written, false on invalid key or write failure.
	 * @throws InvalidArgumentException     When the key is not a non-empty string.
	 */
	public function set( $key, $value, $ttl = null ) {
		if ( ! $this->is_valid_key_value( $key ) ) {
			return false;
		}

		return $this->wp_filesystem->put_contents(
			$this->compile_file_path( $key ),
			serialize( $this->compile_cache_item( $key, $value, $ttl ) ) // phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
		);
	}

	/**
	 * Attempts to get from cache, returning default if nothing found or the
	 * cached entry has expired.
	 *
	 * @param string $key           Cache key to look up.
	 * @param mixed  $default_value Fallback to return when the key is missing or expired.
	 * @return mixed                The cached value, or $default_value.
	 * @throws InvalidArgumentException On invalid key.
	 */
	public function get( $key, $default_value = null ) {
		if ( ! $this->is_valid_key_value( $key ) ) {
			return $default_value;
		}
		$contents = $this->get_contents( $key );
		return $contents ? $contents->data : $default_value;
	}

	/**
	 * Clears a defined cached instance.
	 *
	 * @param string $key Cache key of the file to remove.
	 * @return bool       True if the cache file was deleted, false on invalid key or filesystem failure.
	 * @throws InvalidArgumentException When the key is not a non-empty string.
	 */
	public function delete( $key ) {
		if ( ! $this->is_valid_key_value( $key ) ) {
			return false;
		}
		return $this->wp_filesystem->delete(
			$this->compile_file_path( $key )
		);
	}

	/**
	 * Clears all cache items from the directory.
	 *
	 * @return bool True if the cache directory (and all files within) was removed successfully.
	 */
	public function clear() {
		return $this->wp_filesystem->delete( $this->filepath, true );
	}

	/**
	 * Gets multiple values, returning the default in lieu of a cached value.
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
	 * Sets multiple values in a key=>value array.
	 *
	 * @param array<string, mixed> $values Map of key => value to write in one batch.
	 * @param int|null             $ttl    Shared lifetime applied to every entry; null or 0 persists with no expiry.
	 * @return bool                        True only if every individual set() call succeeded.
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
	 * Deletes multiple keys
	 *
	 * @param array<int, string> $keys List of cache keys to remove.
	 * @return bool                    True only if every individual delete() call succeeded.
	 */
	public function deleteMultiple( $keys ) {
		return $this->all_true(
			array_map(
				function ( $key ) {
					return $this->delete( $key );
				},
				$keys
			)
		);
	}


	/**
	 * Parses the file name based on the settings and key
	 *
	 * @param string $filename Cache key used as the bare file name (without directory or extension).
	 * @return string          Normalised absolute path of the cache file on disk.
	 */
	protected function compile_file_path( string $filename ): string {
		return \wp_normalize_path(
			sprintf(
				'%s/%s%s',
				$this->filepath,
				$filename,
				$this->extension
			)
		);
	}

	/**
	 * Compiles the cache item object
	 *
	 * @param string                 $key  Cache key stored inside the item (used later to validate reads).
	 * @param mixed                  $data Value to be wrapped for serialization.
	 * @param null|int|\DateInterval $ttl  Lifetime; converted to seconds then added to the current timestamp.
	 * @return Cache_Item                  Fully populated Cache_Item ready to be serialized to disk.
	 */
	protected function compile_cache_item( string $key, $data, $ttl ): Cache_Item {
		return new Cache_Item(
			$key,
			$data,
			$this->compile_expiry( $this->ttl_to_seconds( $ttl ) )
		);
	}

	/**
	 * Composes the expiry time with time added to current timestamp.
	 *
	 * @param int $expiry TTL in seconds (0 means the entry never expires).
	 * @return int        Absolute unix timestamp at which the item expires, or 0 for no expiry.
	 */
	protected function compile_expiry( int $expiry ): int {
		return $expiry !== 0 ? $expiry + time() : 0;
	}


	/**
	 * Validates the contents of a file read.
	 * Checks key matches, has data and hasnt expired.
	 *
	 * @param string     $key  Cache key the caller expects this item to belong to.
	 * @param Cache_Item $data Unserialized cache item read from disk.
	 * @return bool            True when the keys match, expiry is numeric and the entry has not expired.
	 */
	protected function validate_contents( string $key, Cache_Item $data ): bool {
		switch ( true ) {
			// Key passes doesnt match cache.
			case $data->key !== $key:
				return false;

			// Expiry isnt numeric
			case ! is_numeric( $data->expiry ):
				return false;

			// If expiry is 0 (Never expires), return true.
			case intval( $data->expiry ) === 0:
				return true;

			// If a timestamp, but expired.
			case intval( $data->expiry ) < time():
				return false;

			default:
				return true;
		}
	}

	/**
	 * Attempts to get the contents from a key.
	 *
	 * @param string $key      Cache key whose file should be read from disk.
	 * @return Cache_Item|null If we have valid data (not expired), the data else null
	 */
	protected function get_contents( string $key ): ?Cache_Item {

		$contents_raw  = $this->wp_filesystem->get_contents( $this->compile_file_path( $key ) );
		$file_contents = $contents_raw ? $contents_raw : '';

		$file_contents = \maybe_unserialize( $file_contents );

		return is_a( $file_contents, Cache_Item::class ) && $this->validate_contents( $key, $file_contents )
			? $file_contents
			: null;
	}
}
