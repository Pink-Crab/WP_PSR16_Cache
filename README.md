# WP PSR16 Simple Cache

Provides both WP Transient and WP FileSystem (Direct) implementation to [*PSR16`s CacheInterface*](https://github.com/php-fig/simple-cache).

![alt text](https://img.shields.io/badge/Current_Version-2.0.3-yellow.svg?style=flat " ") 
[![Open Source Love](https://badges.frapsoft.com/os/mit/mit.svg?v=102)]()

![](https://github.com/Pink-Crab/WP_PSR16_Cache/workflows/GitHub_CI/badge.svg " ")
[![codecov](https://codecov.io/gh/Pink-Crab/WP_PSR16_Cache/branch/master/graph/badge.svg?token=DZOCZVPKBN)](https://codecov.io/gh/Pink-Crab/WP_PSR16_Cache)

***********************************************

## Requirements

Requires Composer and WordPress.

Works with PHP versions 7.1, 7.2, 7.3 & 7.4

***********************************************
## Installation

``` bash
$ composer require pinkcrab/wp-psr16-cache
```



## Getting Started

Once you have the package installed and your autoloader has been included. 

### File Cache

``` php
use PinkCrab\WP_PSR16_Cache\File_Cache;
use PinkCrab\WP_PSR16_Cache\Transient_Cache;

// FILE CACHE
// Creates directory at path passed, if it doesn't exist.
$cache = new File_Cache('path/to/dir', '.do');

// TRANSIENT CACHE 
// Created with optional groups, adding a prefix to transient keys and set file extension.
$cache = new Transient_Cache('group_prefix' ); 

// Set single item to cache.
$cache->set( 'cache_key', $data, 24 * HOURS_IN_SECONDS );

// Gets the value, if not set or expired returns null
$cache->get( 'cache_key', 'fallback' );

// Returns if valid cache item exists.
$cache->has( 'cache_key' );

// Deletes a cache if it exists.
$cahe->delete( 'cache_key' );


// Set multiple values, with a single expiry
$cache->setMultiple( ['key1' => 'Value1', 'key2' => 42], 1 * HOURS_IN_SECONDS );

// Get multiple values in a key => value array, with a shared default.
$cache->getMultiple( ['key1', 'key2'], 'FALLBACK' );

// Clears multiple keys.
$cache->deleteMultiple( ['key1', 'key2'] );

// Clear all cache items
$cache->clear();

```



## File_Cache

> Will create the defined base directory when the object is created. 

The constructor takes 2 properties the path and the file extension. By default the file extension is **.do**.

```php
$wp_uploads = wp_upload_dir();

$cache = new File_Cache($wp_uploads['basedir'] . '/my-cache', '.cache');

$cache->set('my_key', ['some', 'data']);

// Creates  /public/wp-content/uploads/my-cache/my_key.cache


```

If you plan on using this as a plugin and want to clean up after an install. You can just create an instance of the Class on activation and then run clear on uninstall

```php
/** 
 * Creates the cache directory
 */
function called_on_activation(){
    new File_Cache($wp_uploads['basedir'] . '/my-cache', '.cache');
}
/**
 * Clears all values form the cache directory.
 * Please note doesn't delete the folder.
 */
function called_on_uninstall(){
    (new File_Cache($wp_uploads['basedir'] . '/my-cache', '.cache'))->clear();
}
```



## Transient Cache

> Makes use of prefixed/grouped transient values. Preventing collisions while still allowing short and clean keys.

The constructor takes a single argument, this denotes the group that your transients will be created using. This can be omitted if you wanted no prefix on your keys.

```php
$cache = new Transient_Cache('my_cache');

$cache->set('my_key', ['some', 'data']);

// Will create a transient which can be recalled using either;
$value = get_transient('my_cache_my_key');
(new Transient_Cache('my_cache'))->get('my_key');


// You can create an instance with no key
$cache = New Transient_Cache();
$cache->set('my_other_key', ['some', 'data']);
// Get
$value = get_transient('my_other_key');
```
> PLEASE NOTE:
Calling clear() will use $wpdb to get all transients from the database and clear any which start with your prefix. If you have no prefix defined, this could clear all of your transients and create some unusual side effected. 

> ALSO: 
Some managed hosts store transients outside of the regular Options table. This can lead to problems when fetching all transients with your prefix.



***********************************************



## Changelog
* 2.0.3 - Fixed missing wp filesystem include
* 2.0.2 - Readme formatting, added in additional tests for 100% coverage.
* 2.0.1 - Fixed trailin comma issue in File_Cache and setup all github CI workflows.
* 2.0.0 - Moved to composer and switched to using WP_FileSystem over raw PHP functions.
