# WP PSR16 Simple Cache

A WordPress-backed PSR-16 `CacheInterface` implementation with two interchangeable drivers: WP transients and the WP `Direct` filesystem. See [PSR-16 SimpleCache](https://github.com/php-fig/simple-cache) for the contract these drivers implement.

[![Latest Stable Version](https://poser.pugx.org/pinkcrab/wp-psr16-cache/v)](https://packagist.org/packages/pinkcrab/wp-psr16-cache) [![Total Downloads](https://poser.pugx.org/pinkcrab/wp-psr16-cache/downloads)](https://packagist.org/packages/pinkcrab/wp-psr16-cache) [![Latest Unstable Version](https://poser.pugx.org/pinkcrab/wp-psr16-cache/v/unstable)](https://packagist.org/packages/pinkcrab/wp-psr16-cache) [![License](https://poser.pugx.org/pinkcrab/wp-psr16-cache/license)](https://packagist.org/packages/pinkcrab/wp-psr16-cache) [![PHP Version Require](https://poser.pugx.org/pinkcrab/wp-psr16-cache/require/php)](https://packagist.org/packages/pinkcrab/wp-psr16-cache)
![GitHub contributors](https://img.shields.io/github/contributors/Pink-Crab/WP_PSR16_Cache?label=Contributors)
![GitHub issues](https://img.shields.io/github/issues-raw/Pink-Crab/WP_PSR16_Cache)
[![WP 6.6 [PHP8.0-8.4] Tests](https://github.com/Pink-Crab/WP_PSR16_Cache/actions/workflows/WP_6_6.yaml/badge.svg)](https://github.com/Pink-Crab/WP_PSR16_Cache/actions/workflows/WP_6_6.yaml)
[![WP 6.7 [PHP8.0-8.4] Tests](https://github.com/Pink-Crab/WP_PSR16_Cache/actions/workflows/WP_6_7.yaml/badge.svg)](https://github.com/Pink-Crab/WP_PSR16_Cache/actions/workflows/WP_6_7.yaml)
[![WP 6.8 [PHP8.0-8.4] Tests](https://github.com/Pink-Crab/WP_PSR16_Cache/actions/workflows/WP_6_8.yaml/badge.svg)](https://github.com/Pink-Crab/WP_PSR16_Cache/actions/workflows/WP_6_8.yaml)
[![WP 6.9 [PHP8.0-8.4] Tests](https://github.com/Pink-Crab/WP_PSR16_Cache/actions/workflows/WP_6_9.yaml/badge.svg)](https://github.com/Pink-Crab/WP_PSR16_Cache/actions/workflows/WP_6_9.yaml)
[![codecov](https://codecov.io/gh/Pink-Crab/WP_PSR16_Cache/graph/badge.svg?token=DZOCZVPKBN)](https://codecov.io/gh/Pink-Crab/WP_PSR16_Cache)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Pink-Crab/WP_PSR16_Cache/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Pink-Crab/WP_PSR16_Cache/?branch=master)

***********************************************

## Requirements

Requires Composer and WordPress.

> **TESTED AGAINST**
> * PHP 8.0, 8.1, 8.2, 8.3 & 8.4
> * WP 6.6, 6.7, 6.8 & 6.9
> * MySQL 8.4

## Installation

``` bash
$ composer require pinkcrab/wp-psr16-cache
```

## Getting Started

Once the package is installed and your autoloader has been included, the two drivers can be used interchangeably through PSR-16's `CacheInterface`.

``` php
use PinkCrab\WP_PSR16_Cache\File_Cache;
use PinkCrab\WP_PSR16_Cache\Transient_Cache;

// FILE CACHE
// Creates the directory at the path passed, if it doesn't exist.
$cache = new File_Cache( 'path/to/dir', '.do' );

// TRANSIENT CACHE
// Optional group adds a prefix to every transient key.
$cache = new Transient_Cache( 'group_prefix' );

// Set a single item.
$cache->set( 'cache_key', $data, 24 * HOURS_IN_SECONDS );

// Get the value; returns the fallback if the key is missing or expired.
$cache->get( 'cache_key', 'fallback' );

// True when a valid (non-expired) cache item exists.
$cache->has( 'cache_key' );

// Delete a single cache entry.
$cache->delete( 'cache_key' );

// Batch writes with a shared expiry.
$cache->setMultiple( array( 'key1' => 'Value1', 'key2' => 42 ), 1 * HOURS_IN_SECONDS );

// Batch reads with a shared default.
$cache->getMultiple( array( 'key1', 'key2' ), 'FALLBACK' );

// Batch delete.
$cache->deleteMultiple( array( 'key1', 'key2' ) );

// Clear every key tracked by this cache instance.
$cache->clear();
```

## File_Cache

> Creates the configured base directory when the object is constructed.

The constructor takes two arguments — the path and the file extension. The extension defaults to **.do**.

```php
$wp_uploads = wp_upload_dir();

$cache = new File_Cache( $wp_uploads['basedir'] . '/my-cache', '.cache' );

$cache->set( 'my_key', array( 'some', 'data' ) );

// Creates  /public/wp-content/uploads/my-cache/my_key.cache
```

A common pattern is to warm the directory on plugin activation and clear it on uninstall:

```php
/**
 * Creates the cache directory.
 */
function called_on_activation() {
    new File_Cache( $wp_uploads['basedir'] . '/my-cache', '.cache' );
}

/**
 * Removes every cache entry in the directory.
 * Note: clear() removes the files inside the directory; the directory itself is left in place.
 */
function called_on_uninstall() {
    ( new File_Cache( $wp_uploads['basedir'] . '/my-cache', '.cache' ) )->clear();
}
```

## Transient Cache

> Uses prefixed/grouped transient values — prevents collisions while still allowing short, clean keys.

The constructor takes a single optional argument: the group name that every transient written through this instance will be prefixed with. Omit it for no prefix.

```php
$cache = new Transient_Cache( 'my_cache' );

$cache->set( 'my_key', array( 'some', 'data' ) );

// The value can be read back via either:
$value = get_transient( 'my_cache_my_key' );
( new Transient_Cache( 'my_cache' ) )->get( 'my_key' );


// Or create an instance with no group prefix:
$cache = new Transient_Cache();
$cache->set( 'my_other_key', array( 'some', 'data' ) );
$value = get_transient( 'my_other_key' );
```

> **PLEASE NOTE**
> `clear()` queries `$wpdb` for every transient whose key matches the configured prefix and deletes them. If no group prefix is set, this can match — and delete — unrelated transients in the same database. Always set a group on a cache you plan to clear.

> **ALSO**
> Some managed hosts store transients outside the regular `options` table. Where that happens, the prefix-scan in `clear()` won't see them and they will not be cleared.

***********************************************

## Changelog
* 2.1.0 - Drop PHP 7.x, require PHP 8.0+. Bump dev deps: phpstan 2.x, phpstan-wordpress 2.x, phpunit 9.x, WP 6.6-6.9 test matrix. Replace the `php.yaml` workflow with WP_6_6 / WP_6_7 / WP_6_8 / WP_6_9 actions (`mysql:8.4`). Add `.scrutinizer.yml`. Force `FS_METHOD=direct` in test config so `File_Cache` consistently gets `WP_Filesystem_Direct` under PHP 8.x. Expand `@param` / `@return` docblocks across the source tree. No public API changes.
* 2.0.4 - Updated dev dependencies and added scrutinizer to CI
* 2.0.3 - Fixed missing wp filesystem include
* 2.0.2 - Readme formatting, added in additional tests for 100% coverage.
* 2.0.1 - Fixed trailin comma issue in File_Cache and setup all github CI workflows.
* 2.0.0 - Moved to composer and switched to using WP_FileSystem over raw PHP functions.
