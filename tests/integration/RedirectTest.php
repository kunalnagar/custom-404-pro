<?php
/**
 * Integration tests for the 404 redirect behaviour.
 *
 * @package Custom_404_Pro
 */

/**
 * Tests AdminClass::custom_404_pro_redirect() against a real WordPress + MySQL environment.
 *
 * Strategy for intercepting wp_safe_redirect():
 *   wp_safe_redirect() fires the 'wp_redirect' filter before sending headers and
 *   calling exit(). Returning false from that filter suppresses the redirect so
 *   the test process does not terminate. We capture the URL and status code in
 *   instance properties for assertions.
 *
 * Strategy for simulating a 404 request:
 *   We set $GLOBALS['wp_query']->is_404 to true, which makes is_404() return
 *   true without needing a real HTTP request.
 */
class C404P_Integration_RedirectTest extends WP_UnitTestCase {

	/**
	 * The redirect URL captured by the wp_redirect filter.
	 *
	 * @var string|null
	 */
	private $redirect_url;

	/**
	 * The HTTP status code captured by the wp_redirect filter.
	 *
	 * @var int|null
	 */
	private $redirect_status;

	/**
	 * AdminClass instance under test.
	 *
	 * @var AdminClass
	 */
	private $admin;

	/**
	 * Helpers instance for setting up option values.
	 *
	 * @var Helpers
	 */
	private $helpers;

	/**
	 * Set up: create the logs table, seed default settings, configure 404 state.
	 */
	public function setUp(): void {
		parent::setUp();

		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		ActivateClass::create_tables();
		ActivateClass::initialize_options(); // seeds wp_options defaults via add_option()

		$this->helpers         = new Helpers();
		$this->admin           = new AdminClass();
		$this->redirect_url    = null;
		$this->redirect_status = null;

		// wp_safe_redirect() calls wp_validate_redirect() which rejects cross-domain
		// URLs (test site is example.org). Allow the domains used in tests so the
		// redirect URL reaches our capture filter unchanged.
		add_filter( 'allowed_redirect_hosts', array( $this, 'allow_test_hosts' ) );

		// Intercept wp_safe_redirect() before it sends headers / calls exit().
		add_filter( 'wp_redirect', array( $this, 'capture_redirect' ), 10, 2 );

		// Make is_404() return true.
		$GLOBALS['wp_query']         = new WP_Query();
		$GLOBALS['wp_query']->is_404 = true;
	}

	/**
	 * Tear down: remove filters, reset globals, drop the logs table.
	 *
	 * Settings are stored in wp_options (DML) so they are rolled back automatically
	 * by WP_UnitTestCase. Only the logs table requires a manual DROP because
	 * CREATE TABLE is DDL and implicitly commits.
	 */
	public function tearDown(): void {
		global $wpdb;

		remove_filter( 'allowed_redirect_hosts', array( $this, 'allow_test_hosts' ) );
		remove_filter( 'wp_redirect', array( $this, 'capture_redirect' ), 10 );

		unset( $GLOBALS['wp_query'] );

		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . $this->helpers->table_logs ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		parent::tearDown();
	}

	/**
	 * allowed_redirect_hosts filter — permits external domains used in tests.
	 *
	 * @param array $hosts Allowed hosts.
	 * @return array
	 */
	public function allow_test_hosts( $hosts ) {
		$hosts[] = 'example.com';
		return $hosts;
	}

	/**
	 * wp_redirect filter callback — captures location/status and cancels redirect.
	 *
	 * @param string $location Redirect URL.
	 * @param int    $status   HTTP status code.
	 * @return false Returning false prevents headers from being sent.
	 */
	public function capture_redirect( $location, $status ) {
		$this->redirect_url    = $location;
		$this->redirect_status = $status;
		return false;
	}

	// -------------------------------------------------------------------------
	// Tests
	// -------------------------------------------------------------------------

	/**
	 * No redirect should occur when mode is empty (default state).
	 */
	public function test_redirect_does_nothing_when_mode_is_empty() {
		// Default value of 'mode' after initialize_options() is ''.
		$this->admin->custom_404_pro_redirect();
		$this->assertNull( $this->redirect_url, 'No redirect should fire when mode is empty.' );
	}

	/**
	 * URL mode should redirect to the configured URL.
	 */
	public function test_redirect_sends_correct_url_in_url_mode() {
		$this->helpers->update_settings( array( 'mode' => 'url', 'mode_url' => 'https://example.com/custom-error' ) );

		$this->admin->custom_404_pro_redirect();

		$this->assertSame( 'https://example.com/custom-error', $this->redirect_url );
	}

	/**
	 * The HTTP status code from the redirect_error_code option should be used.
	 */
	public function test_redirect_uses_configured_status_code() {
		$this->helpers->update_settings( array( 'mode' => 'url', 'mode_url' => 'https://example.com', 'redirect_error_code' => 301 ) );

		$this->admin->custom_404_pro_redirect();

		$this->assertSame( 301, $this->redirect_status );
	}

	/**
	 * Page mode should redirect to the GUID of the configured WordPress page.
	 */
	public function test_redirect_sends_page_guid_in_page_mode() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'Custom 404 Page',
			)
		);
		$page    = get_post( $page_id );

		$this->helpers->update_settings( array( 'mode' => 'page', 'mode_page' => (string) $page_id ) );

		$this->admin->custom_404_pro_redirect();

		$this->assertSame( $page->guid, $this->redirect_url );
	}

	/**
	 * No redirect should fire when the current request is not a 404.
	 */
	public function test_redirect_does_not_fire_when_not_404() {
		$GLOBALS['wp_query']->is_404 = false;

		$this->helpers->update_settings( array( 'mode' => 'url', 'mode_url' => 'https://example.com' ) );

		$this->admin->custom_404_pro_redirect();

		$this->assertNull( $this->redirect_url, 'No redirect should fire for non-404 requests.' );
	}

	/**
	 * A log entry should be created when logging is enabled.
	 */
	public function test_redirect_creates_log_entry_when_logging_enabled() {
		$this->helpers->update_settings( array( 'logging_enabled' => true, 'mode' => 'url', 'mode_url' => 'https://example.com' ) );

		$this->admin->custom_404_pro_redirect();

		$logs = $this->helpers->get_logs();
		$this->assertNotEmpty( $logs, 'A log entry should be created when logging is enabled.' );
	}

	/**
	 * No log entry should be created when logging is disabled (default).
	 */
	public function test_redirect_does_not_log_when_logging_disabled() {
		// Default logging_enabled is false after initialize_options().
		$this->helpers->update_settings( array( 'mode' => 'url', 'mode_url' => 'https://example.com' ) );

		$this->admin->custom_404_pro_redirect();

		$logs = $this->helpers->get_logs();
		$this->assertEmpty( $logs, 'No log entry should be created when logging is disabled.' );
	}

	// -------------------------------------------------------------------------
	// Email cooldown tests
	// -------------------------------------------------------------------------

	/**
	 * The cooldown transient should be set after a notification email is sent.
	 */
	public function test_email_cooldown_transient_is_set_after_notification_sent() {
		$this->helpers->update_settings(
			array(
				'logging_enabled' => true,
				'send_email'      => true,
				'mode'            => 'url',
				'mode_url'        => 'https://example.com',
			)
		);

		$this->assertFalse(
			get_transient( 'custom_404_pro_email_cooldown' ),
			'Cooldown transient should not exist before the first 404.'
		);

		$this->admin->custom_404_pro_redirect();

		$this->assertNotFalse(
			get_transient( 'custom_404_pro_email_cooldown' ),
			'Cooldown transient should be set after an email notification is sent.'
		);
	}

	/**
	 * No email should be sent when the cooldown transient is already active.
	 *
	 * Uses a wp_mail filter to count how many times wp_mail() is invoked.
	 */
	public function test_email_not_sent_during_active_cooldown() {
		$this->helpers->update_settings(
			array(
				'logging_enabled' => true,
				'send_email'      => true,
				'mode'            => 'url',
				'mode_url'        => 'https://example.com',
			)
		);

		// Pre-set the cooldown transient to simulate a recent send.
		set_transient( 'custom_404_pro_email_cooldown', true, HOUR_IN_SECONDS );

		$mail_count = 0;
		$counter    = function ( $args ) use ( &$mail_count ) {
			++$mail_count;
			return $args;
		};
		add_filter( 'wp_mail', $counter );

		$this->admin->custom_404_pro_redirect();

		remove_filter( 'wp_mail', $counter );

		$this->assertSame( 0, $mail_count, 'No email should be sent while the cooldown transient is active.' );
	}

	/**
	 * Exactly one email should be sent on the first 404, and none on the second.
	 */
	public function test_only_one_email_sent_across_two_consecutive_404s() {
		$this->helpers->update_settings(
			array(
				'logging_enabled' => true,
				'send_email'      => true,
				'mode'            => 'url',
				'mode_url'        => 'https://example.com',
			)
		);

		$mail_count = 0;
		$counter    = function ( $args ) use ( &$mail_count ) {
			++$mail_count;
			return $args;
		};
		add_filter( 'wp_mail', $counter );

		// First 404 — should trigger an email and set the transient.
		$this->admin->custom_404_pro_redirect();

		// Second 404 — transient is now active, email should be suppressed.
		$this->admin->custom_404_pro_redirect();

		remove_filter( 'wp_mail', $counter );

		$this->assertSame( 1, $mail_count, 'Exactly one email should be sent across two consecutive 404s.' );
	}
}
