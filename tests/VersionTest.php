<?php
/**
 * Unit tests asserting the release metadata is internally consistent.
 *
 * A version bump has to land in four places at once: the plugin header, the
 * PHP constant, the readme's Stable tag and the readme changelog. Missing one
 * ships a release that WordPress.org and the update checker disagree about.
 * These tests fail the build when they drift apart.
 *
 * @package Custom_404_Pro
 */

use PHPUnit\Framework\TestCase;

/**
 * Release metadata test case.
 */
class VersionTest extends TestCase {

	/**
	 * Contents of the main plugin file.
	 *
	 * @var string
	 */
	private $plugin_file;

	/**
	 * Contents of readme.txt.
	 *
	 * @var string
	 */
	private $readme;

	/**
	 * Loads the plugin file and readme once per test.
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->plugin_file = file_get_contents( dirname( __DIR__ ) . '/custom-404-pro.php' );
		$this->readme      = file_get_contents( dirname( __DIR__ ) . '/readme.txt' );
	}

	/**
	 * Returns the first capture group of a pattern matched against a subject.
	 *
	 * @param string $pattern Regular expression with one capture group.
	 * @param string $subject Text to search.
	 * @param string $label   Human-readable name used in the failure message.
	 * @return string The captured value.
	 */
	private function capture( string $pattern, string $subject, string $label ): string {
		$matched = preg_match( $pattern, $subject, $matches );
		$this->assertSame( 1, $matched, "Could not find {$label}." );
		return trim( $matches[1] );
	}

	// -------------------------------------------------------------------------
	// Version consistency
	// -------------------------------------------------------------------------

	/**
	 * The plugin header version and the PHP constant must agree.
	 */
	public function test_plugin_header_version_matches_version_constant() {
		$header   = $this->capture( '/^\s*\*\s*Version:\s*(.+)$/m', $this->plugin_file, 'the Version: plugin header' );
		$constant = $this->capture( "/define\(\s*'CUSTOM_404_PRO_VERSION',\s*'([^']+)'\s*\)/", $this->plugin_file, 'the CUSTOM_404_PRO_VERSION constant' );

		$this->assertSame( $header, $constant, 'Plugin header Version and CUSTOM_404_PRO_VERSION must match.' );
	}

	/**
	 * The readme Stable tag must point at the version being shipped.
	 */
	public function test_readme_stable_tag_matches_plugin_version() {
		$header      = $this->capture( '/^\s*\*\s*Version:\s*(.+)$/m', $this->plugin_file, 'the Version: plugin header' );
		$stable_tag  = $this->capture( '/^Stable tag:\s*(.+)$/m', $this->readme, 'the Stable tag' );

		$this->assertSame( $header, $stable_tag, 'readme.txt Stable tag must match the plugin version.' );
	}

	/**
	 * Stable tag must name a real release, never trunk.
	 */
	public function test_readme_stable_tag_is_not_trunk() {
		$stable_tag = $this->capture( '/^Stable tag:\s*(.+)$/m', $this->readme, 'the Stable tag' );
		$this->assertNotSame( 'trunk', strtolower( $stable_tag ), 'Stable tag must point at a tagged release, not trunk.' );
		$this->assertMatchesRegularExpression( '/^\d+\.\d+\.\d+$/', $stable_tag, 'Stable tag must be a semantic version.' );
	}

	/**
	 * The version being shipped must have its own changelog entry.
	 */
	public function test_current_version_has_a_changelog_entry() {
		$version = $this->capture( "/define\(\s*'CUSTOM_404_PRO_VERSION',\s*'([^']+)'\s*\)/", $this->plugin_file, 'the CUSTOM_404_PRO_VERSION constant' );

		$this->assertStringContainsString(
			'= ' . $version . ' =',
			$this->readme,
			"readme.txt must contain a changelog entry for version {$version}."
		);
	}

	// -------------------------------------------------------------------------
	// Compatibility declarations
	// -------------------------------------------------------------------------

	/**
	 * The plugin header must declare a minimum WordPress version.
	 *
	 * WordPress uses this to block updates on incompatible sites. Without it,
	 * an unsupported install downloads the update and fataled instead.
	 */
	public function test_plugin_header_declares_requires_at_least() {
		$this->assertMatchesRegularExpression(
			'/^\s*\*\s*Requires at least:\s*\d+\.\d+/m',
			$this->plugin_file,
			'The plugin header must declare "Requires at least".'
		);
	}

	/**
	 * The plugin header must declare a minimum PHP version.
	 */
	public function test_plugin_header_declares_requires_php() {
		$this->assertMatchesRegularExpression(
			'/^\s*\*\s*Requires PHP:\s*\d+\.\d+/m',
			$this->plugin_file,
			'The plugin header must declare "Requires PHP".'
		);
	}

	/**
	 * The plugin header and the readme must declare the same requirements.
	 */
	public function test_plugin_header_requirements_match_readme() {
		$header_wp  = $this->capture( '/^\s*\*\s*Requires at least:\s*(.+)$/m', $this->plugin_file, 'the header Requires at least' );
		$readme_wp  = $this->capture( '/^Requires at least:\s*(.+)$/m', $this->readme, 'the readme Requires at least' );
		$header_php = $this->capture( '/^\s*\*\s*Requires PHP:\s*(.+)$/m', $this->plugin_file, 'the header Requires PHP' );
		$readme_php = $this->capture( '/^Requires PHP:\s*(.+)$/m', $this->readme, 'the readme Requires PHP' );

		$this->assertSame( $readme_wp, $header_wp, 'Requires at least must match between the plugin header and readme.txt.' );
		$this->assertSame( $readme_php, $header_php, 'Requires PHP must match between the plugin header and readme.txt.' );
	}

	/**
	 * "Tested up to" must name the WordPress release this plugin was verified against.
	 *
	 * WordPress.org shows a compatibility warning once this falls more than
	 * three major releases behind current core.
	 */
	public function test_readme_is_tested_up_to_wordpress_7_1() {
		$tested = $this->capture( '/^Tested up to:\s*(.+)$/m', $this->readme, 'the Tested up to value' );
		$this->assertSame( '7.1', $tested, 'readme.txt must declare compatibility with WordPress 7.1.' );
	}
}
