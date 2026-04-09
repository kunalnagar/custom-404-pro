<?php
/**
 * Unit tests for the PluginClass class.
 *
 * @package Custom_404_Pro
 */

use PHPUnit\Framework\TestCase;

/**
 * PluginClass test case.
 */
class PluginClassTest extends TestCase {

	/**
	 * Resets action and textdomain stubs before each test.
	 */
	protected function setUp(): void {
		parent::setUp();
		$GLOBALS['_test_actions']                 = array();
		$GLOBALS['_load_plugin_textdomain_calls'] = array();
	}

	/**
	 * Asserts that load_textdomain is a public method.
	 */
	public function test_load_textdomain_is_public_method() {
		$ref    = new ReflectionClass( PluginClass::class );
		$method = $ref->getMethod( 'load_textdomain' );
		$this->assertTrue( $method->isPublic(), 'load_textdomain must be public so the plugins_loaded hook can call it' );
	}

	/**
	 * Asserts that plugins_loaded action is registered with load_textdomain on construction.
	 */
	public function test_plugins_loaded_action_is_registered_on_construction() {
		new PluginClass();

		$this->assertArrayHasKey( 'plugins_loaded', $GLOBALS['_test_actions'], 'plugins_loaded action must be registered' );

		$found = false;
		foreach ( $GLOBALS['_test_actions']['plugins_loaded'] as $entry ) {
			$cb = $entry[0];
			if ( is_array( $cb ) && $cb[0] instanceof PluginClass && 'load_textdomain' === $cb[1] ) {
				$found = true;
				break;
			}
		}
		$this->assertTrue( $found, 'plugins_loaded must be hooked to PluginClass::load_textdomain' );
	}

	/**
	 * Asserts that load_textdomain calls load_plugin_textdomain with the correct text domain.
	 */
	public function test_load_textdomain_uses_correct_text_domain() {
		$plugin = new PluginClass();
		$plugin->load_textdomain();

		$this->assertNotEmpty( $GLOBALS['_load_plugin_textdomain_calls'], 'load_plugin_textdomain must be called' );
		$this->assertSame(
			'custom-404-pro',
			$GLOBALS['_load_plugin_textdomain_calls'][0]['domain'],
			'Text domain must be "custom-404-pro"'
		);
	}

	/**
	 * Asserts that load_textdomain passes a languages path ending in /languages.
	 */
	public function test_load_textdomain_passes_languages_path() {
		$plugin = new PluginClass();
		$plugin->load_textdomain();

		$rel_path = $GLOBALS['_load_plugin_textdomain_calls'][0]['plugin_rel_path'];
		$this->assertStringEndsWith( '/languages', $rel_path, 'plugin_rel_path must end with /languages' );
	}

	/**
	 * Asserts that load_textdomain can be called more than once without error.
	 */
	public function test_load_textdomain_is_idempotent() {
		$plugin = new PluginClass();
		$plugin->load_textdomain();
		$plugin->load_textdomain();

		$this->assertCount( 2, $GLOBALS['_load_plugin_textdomain_calls'] );
	}
}
