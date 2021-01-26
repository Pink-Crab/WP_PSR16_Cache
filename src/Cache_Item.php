<?php

declare(strict_types=1);


/**
 * Cache item class, used for FileCache
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

class Cache_Item {

	/**
	 * The cache items key
	 *
	 * @var string
	 */
	public $key = '';

	/**
	 * The data to be cached
	 *
	 * @var mixed
	 */
	public $data = null;

	/**
	 * Expiry time (timestamp)
	 *
	 * @var int|null
	 */
	public $expiry;

	/**
	 * Creates an instance of a Cache Item
	 *
	 * @param string $key
	 * @param mixed $data
	 * @param int $expiry
	 */
	public function __construct( string $key, $data, int $expiry ) {
		$this->key    = $key;
		$this->data   = $data;
		$this->expiry = $expiry;
	}
}
