<?php
/**
 * PNFW_Migration_OneSignal.
 *
 * @since   0.1.0
 * @package PNFW_Migration_OneSignal
 */
class PNFW_Migration_OneSignal_Test extends WP_UnitTestCase {

	/**
	 * Test if our class exists.
	 *
	 * @since  0.1.0
	 */
	function test_class_exists() {
		$this->assertTrue( class_exists( 'PNFW_Migration_OneSignal') );
	}

	/**
	 * Test that our main helper function is an instance of our class.
	 *
	 * @since  0.1.0
	 */
	function test_get_instance() {
		$this->assertInstanceOf(  'PNFW_Migration_OneSignal', pnfw_migration_onesignal() );
	}

	/**
	 * Replace this with some actual testing code.
	 *
	 * @since  0.1.0
	 */
	function test_sample() {
		$this->assertTrue( true );
	}
}
