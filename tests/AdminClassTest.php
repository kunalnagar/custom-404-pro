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
	 * Resets multilingual stubs, filter registry, and transient store before each test.
	 */
	protected function setUp(): void {
		parent::setUp();
		$GLOBALS['_test_filters']        = array();
		$GLOBALS['_test_transients']     = array();
		$GLOBALS['_pll_get_post_return'] = false;
		unset( $GLOBALS['_pll_current_language'] );
		unset( $GLOBALS['_pll_default_language'] );
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

	// ------------------------------------------------------------------
	// normalize_page_id_to_default_language
	// ------------------------------------------------------------------

	/**
	 * Calls the private normalize_page_id_to_default_language method via reflection.
	 *
	 * @param AdminClass $admin   Instance to invoke on.
	 * @param int        $page_id Page ID to normalize.
	 * @return int
	 */
	private function normalize( AdminClass $admin, int $page_id ): int {
		$ref    = new ReflectionClass( $admin );
		$method = $ref->getMethod( 'normalize_page_id_to_default_language' );
		$method->setAccessible( true );
		return $method->invoke( $admin, $page_id );
	}

	/**
	 * Asserts that the original ID is returned when no multilingual plugin is active.
	 */
	public function test_normalize_returns_original_id_when_no_multilingual_plugin_active() {
		$admin = new AdminClass();
		$this->assertSame( 42, $this->normalize( $admin, 42 ) );
	}

	/**
	 * Asserts that the WPML default-language ID is returned when a WPML filter is registered.
	 */
	public function test_normalize_returns_default_language_id_via_wpml() {
		// Register wpml_default_language filter.
		add_filter( 'wpml_default_language', function () { return 'en'; } );
		// Register wpml_object_id filter that returns the default-language page ID.
		add_filter(
			'wpml_object_id',
			function ( $page_id ) {
				return 5; // Simulate WPML returning the English (default) page for any input.
			}
		);
		$admin = new AdminClass();
		$this->assertSame( 5, $this->normalize( $admin, 20 ) );
	}

	/**
	 * Asserts that the Polylang default-language ID is returned when pll_get_post finds a translation.
	 */
	public function test_normalize_returns_default_language_id_via_polylang() {
		$GLOBALS['_pll_default_language'] = 'en';
		$GLOBALS['_pll_get_post_return']  = 10; // pll_get_post returns the default-language page.
		$admin = new AdminClass();
		$this->assertSame( 10, $this->normalize( $admin, 20 ) );
	}

	/**
	 * Asserts that the original ID is returned when Polylang finds no default-language translation.
	 */
	public function test_normalize_returns_original_id_when_polylang_finds_no_default_translation() {
		$GLOBALS['_pll_default_language'] = 'en';
		$GLOBALS['_pll_get_post_return']  = false; // pll_get_post returns false → no translation.
		$admin = new AdminClass();
		$this->assertSame( 20, $this->normalize( $admin, 20 ) );
	}

	// ------------------------------------------------------------------
	// is_email_on_cooldown
	// ------------------------------------------------------------------

	/**
	 * Asserts that is_email_on_cooldown() returns false when no transient has been set.
	 */
	public function test_is_email_on_cooldown_returns_false_when_no_transient_set() {
		$admin = new AdminClass();
		$this->assertFalse( $admin->is_email_on_cooldown() );
	}

	/**
	 * Asserts that is_email_on_cooldown() returns true when the cooldown transient is set.
	 */
	public function test_is_email_on_cooldown_returns_true_when_transient_is_set() {
		set_transient( 'custom_404_pro_email_cooldown', true, HOUR_IN_SECONDS );
		$admin = new AdminClass();
		$this->assertTrue( $admin->is_email_on_cooldown() );
	}

	/**
	 * Asserts that is_email_on_cooldown() returns false after the cooldown transient is deleted.
	 */
	public function test_is_email_on_cooldown_returns_false_after_transient_deleted() {
		set_transient( 'custom_404_pro_email_cooldown', true, HOUR_IN_SECONDS );
		delete_transient( 'custom_404_pro_email_cooldown' );
		$admin = new AdminClass();
		$this->assertFalse( $admin->is_email_on_cooldown() );
	}
}
