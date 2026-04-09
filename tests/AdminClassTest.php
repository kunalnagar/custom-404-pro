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

	/**
	 * Resets multilingual stubs and filter registry before each test.
	 */
	protected function setUp(): void {
		parent::setUp();
		$GLOBALS['_test_filters']        = array();
		$GLOBALS['_pll_get_post_return'] = false;
		unset( $GLOBALS['_pll_current_language'] );
	}

	/**
	 * Asserts that the original page ID is returned when Polylang finds no translation.
	 */
	public function test_resolve_multilingual_page_id_returns_original_when_polylang_finds_no_translation() {
		$GLOBALS['_pll_get_post_return'] = false; // pll_get_post returns false → no translation.
		$admin                           = new AdminClass();
		$this->assertSame( 42, $admin->resolve_multilingual_page_id( 42 ) );
	}

	/**
	 * Asserts that the translated page ID is returned when Polylang finds a translation.
	 */
	public function test_resolve_multilingual_page_id_returns_translated_id_from_polylang() {
		$GLOBALS['_pll_get_post_return']   = 99;
		$GLOBALS['_pll_current_language']  = 'fr';
		$admin                             = new AdminClass();
		$this->assertSame( 99, $admin->resolve_multilingual_page_id( 42 ) );
	}

	/**
	 * Asserts that the translated page ID is returned when a WPML filter is registered.
	 */
	public function test_resolve_multilingual_page_id_returns_translated_id_from_wpml_filter() {
		add_filter(
			'wpml_object_id',
			function ( $page_id ) {
				return 77; // Simulate WPML returning the translated page ID.
			}
		);
		$admin = new AdminClass();
		$this->assertSame( 77, $admin->resolve_multilingual_page_id( 42 ) );
	}

	/**
	 * Asserts that the original page ID is returned when no WPML filter is registered.
	 */
	public function test_resolve_multilingual_page_id_returns_original_when_no_wpml_filter_registered() {
		$admin = new AdminClass();
		$this->assertSame( 42, $admin->resolve_multilingual_page_id( 42 ) );
	}
}
