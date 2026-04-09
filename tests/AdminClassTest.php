<?php
/**
 * Unit tests for the AdminClass class.
 *
 * @package Custom_404_Pro
 */

use PHPUnit\Framework\TestCase;

/**
 * AdminClass test case.
 */
class AdminClassTest extends TestCase {

	/**
	 * Asserts that helpers is an explicitly declared property.
	 */
	public function test_helpers_is_declared_property() {
		$ref = new ReflectionClass( AdminClass::class );
		$this->assertTrue(
			$ref->hasProperty( 'helpers' ),
			'helpers must be explicitly declared to avoid PHP 8.2+ deprecation'
		);
	}

	/**
	 * Asserts that helpers is an instance of Helpers after construction.
	 */
	public function test_helpers_is_instance_of_helpers_after_construction() {
		$admin = new AdminClass();
		$ref   = new ReflectionClass( $admin );
		$prop  = $ref->getProperty( 'helpers' );
		$prop->setAccessible( true );
		$this->assertInstanceOf( Helpers::class, $prop->getValue( $admin ) );
	}
}
