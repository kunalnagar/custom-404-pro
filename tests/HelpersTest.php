<?php
/**
 * Unit tests for the Helpers class.
 *
 * @package Custom_404_Pro
 */

use PHPUnit\Framework\TestCase;

/**
 * Helpers test case.
 */
class HelpersTest extends TestCase {

	/**
	 * Asserts that table_options is an explicitly declared property.
	 */
	public function test_table_options_is_declared_property() {
		$ref = new ReflectionClass( Helpers::class );
		$this->assertTrue(
			$ref->hasProperty( 'table_options' ),
			'table_options must be explicitly declared to avoid PHP 8.2+ deprecation'
		);
	}

	/**
	 * Asserts that table_logs is an explicitly declared property.
	 */
	public function test_table_logs_is_declared_property() {
		$ref = new ReflectionClass( Helpers::class );
		$this->assertTrue(
			$ref->hasProperty( 'table_logs' ),
			'table_logs must be explicitly declared to avoid PHP 8.2+ deprecation'
		);
	}

	/**
	 * Asserts that options_defaults is an explicitly declared property.
	 */
	public function test_options_defaults_is_declared_property() {
		$ref = new ReflectionClass( Helpers::class );
		$this->assertTrue(
			$ref->hasProperty( 'options_defaults' ),
			'options_defaults must be explicitly declared to avoid PHP 8.2+ deprecation'
		);
	}

	/**
	 * Asserts that properties have expected values after construction.
	 */
	public function test_properties_have_correct_values_after_construction() {
		$helpers = new Helpers();
		$this->assertSame( 'custom_404_pro_options', $helpers->table_options );
		$this->assertSame( 'custom_404_pro_logs', $helpers->table_logs );
		$this->assertIsArray( $helpers->options_defaults );
		$this->assertNotEmpty( $helpers->options_defaults );
	}
}
