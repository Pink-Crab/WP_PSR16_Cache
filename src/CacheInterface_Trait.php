<?php declare(strict_types=1);
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

use DateInterval;
use InvalidArgumentException;

trait CacheInterface_Trait {

	/**
	 * Checks if the passed key is a valid key value.
	 *
	 * @param mixed $key
	 * @return bool
	 * @throws InvalidArgumentException
	 */
	protected function is_valid_key_value( $key ): bool {
		if ( ! is_string( $key ) || empty( $key ) ) {
			throw new InvalidArgumentException( 'Key must be a valid string' );
		}

		return (bool) preg_match( '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $key );
	}

	/**
	 * Converts a ttl ( DateInterval, int )
	 *
	 * @param mixed $ttl
	 * @return int
	 */
	public function ttl_to_seconds( $ttl ):int {
		switch ( true ) {
			case is_a( $ttl, DateInterval::class ):
				$days  = (int) $ttl->format( '%a' );
				$hours = (int) $ttl->format( '%h' );
				$mins  = (int) $ttl->format( '%i' );
				$secs  = (int) $ttl->format( '%s' );

				return ( $days * 24 * 60 * 60 )
					+ ( $hours * 60 * 60 )
					+ ( $mins * 60 )
					+ $secs;

			case is_numeric( $ttl ):
				return (int) $ttl;

			default:
				return 0;
		}
	}

	/**
	 * Checks if all values in an array are true and boosl
	 *
	 * @param array<mixed> $array
	 * @return bool
	 */
	protected function all_true( array $array ): bool {
		foreach ( $array as $value ) {
			if ( ! is_bool( $value ) ) {
				return false;
			}

			if ( $value === false ) {
				return false;
			}
		}
		return true;
	}
}
