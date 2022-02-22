<?php
/**
 * Plugin installer.
 *
 * @copyright (c) 2021, Code Atlantic LLC.
 *
 * @package ContentControls
 */

namespace ContentControl;

defined( 'ABSPATH' ) || exit;

/**
 * Class Install
 *
 * @since 1.0.0
 */
class Install {

	/**
	 * Activation wrapper.
	 *
	 * @param bool $network_wide Weather to activate network wide.
	 */
	public static function activate_plugin( $network_wide ) {
		self::do_multisite( $network_wide, [ __CLASS__, 'activate_site' ] );
	}

	/**
	 * Deactivation wrapper.
	 *
	 * @param bool $network_wide Weather to deactivate network wide.
	 */
	public static function deactivate_plugin( $network_wide ) {
		self::do_multisite( $network_wide, [ __CLASS__, 'deactivate_site' ] );
	}

	/**
	 * Uninstall the plugin.
	 */
	public static function uninstall_plugin() {
		self::do_multisite( true, [ __CLASS__, 'uninstall_site' ] );
	}

	/**
	 * Handle single & multisite processes.
	 *
	 * @param bool     $network_wide Weather to do it network wide.
	 * @param callable $method Callable method for each site.
	 * @param array    $args Array of extra args.
	 */
	private static function do_multisite( $network_wide, $method, $args = [] ) {
		global $wpdb;

		if ( is_multisite() && $network_wide ) {
			$activated = get_site_option( 'content_control_activated', [] );

			/* phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery */
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

			// Try to reduce the chances of a timeout with a large number of sites.
			if ( \count( $blog_ids ) > 2 ) {
				ignore_user_abort( true );

				if ( ! \ContentControl\is_func_disabled( 'set_time_limit' ) ) {
					/* phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged */
					@set_time_limit( 0 );
				}
			}

			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				call_user_func_array( $method, [ $args ] );

				$activated[] = $blog_id;

				restore_current_blog();
			}

			update_site_option( 'content_control_activated', $activated );
		} else {
			call_user_func_array( $method, [ $args ] );
		}
	}

	/**
	 * Activate on single site.
	 */
	public static function activate_site() {
		// Add a temporary option that will fire a hookable action on next load.
		set_transient( '_content_control_installed', true, 3600 );
	}

	/**
	 * Deactivate on single site.
	 */
	public static function deactivate_site() {
	}

	/**
	 * Uninstall single site.
	 */
	public static function uninstall_site() {
	}

}
