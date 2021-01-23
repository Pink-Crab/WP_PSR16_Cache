<?php

/**
 * Basics setup test to ensure WP_Unit is working and isntalled correctly.
 */

namespace PinkCrab\Cache\Tests;

use ReflectionClass;
use WP_UnitTestCase;

class Test_Test extends WP_UnitTestCase {

	function test_wordpress_and_plugin_are_loaded() {
		$this->assertTrue( function_exists( 'do_action' ) );
	}

	function test_wp_phpunit_is_loaded_via_composer() {
		$this->assertStringStartsWith(
			dirname( __DIR__ ) . '/vendor/',
			getenv( 'WP_PHPUNIT__DIR' )
		);

		$this->assertStringStartsWith(
			dirname( __DIR__ ) . '/vendor/',
			( new ReflectionClass( 'WP_UnitTestCase' ) )->getFileName()
		);
	}
}
