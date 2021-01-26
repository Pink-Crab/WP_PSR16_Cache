# WP PSR16 Simple Cache

Provides both WP Transient and WP FileSystem (Direct) implementation to PSR16`s *CacheInterface*.

## Requirements

Requires Composer and WordPress.

## Installation

``` bash
$ composer require pinkcrab/wp-psr16-cache
```

## Getting Started

Once you have the package installed and your autloader has been included. 

### File Cache

``` php
use PinkCrab\WP_PSR16_Cache\File_Cache;
use PinkCrab\WP_PSR16_Cache\Transient_Cache;

// FILE CACHE
// Creates directory at path passed, if it doesnt exist.
$cache = new File_Cache('path/to/dir');

// TRANSIENT CACHE 
// Created with optonal groups, adding a prefix to transient keys and set file extension.
$cache = new Transient_Cache('group_prefix', '.do' ); 

// Set single item to cache.
$cache->set( 'cache_key', $data, 24 * HOURS_IN_SECONDS );

// Gets the value, if not set or expired returns null
$cache->get( 'cache_key', 'fallback' );

// Returns if valid cache item exists.
$cache->has( 'cache_key' );

// Deletes a cache if it exists.
$cahe->delete( 'cache_key' );


// Set mutiple values, with a single expiry
$cache->setMultiple( ['key1' => 'Value1', 'key2' => 42], 1 * HOURS_IN_SECONDS );

// Get multiple values in a key => value array, with a shared default.
$cache->getMultiple( ['key1', 'key2'], 'FALLBACK' );

// Clears multiple keys.
$cache->deleteMultiple( ['key1', 'key2'] );

// Clear all cache items
$cache->clear();

```
