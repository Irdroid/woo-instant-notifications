<?php

/**
 * Plugin Name: Woo Instant Notifications
 * Plugin URI: https://github.com/irdroid/woo-instant-notifications
 * Description: A woocommerce add on to support customize notifications
 * Version: 1.0
 * Author: Irdroid
 * Author URI: http://irdroid.com
 * Requires at least: 4.9
 * Tested up to: 4.9.5
 *
 * Text Domain: woo-instant-notifications
 *
 * @package Woo_Instant_Notifications
 * @category Core
 * @author Bakalski
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Woo_Instant_Notifications' ) ) {

	/**
	 * Main Woo Instant Notifications Class
	 *
	 * @class Woo_Instant_Notifications
	 * @version	2.0.3
	 */
	final class Woo_Instant_Notifications {


		public $version = '1.0';
		/**
		 * @var Woo_Instant_Notifications The single instance of the class
		 * @since 2.1
		 */
		protected static $_instance = null;

		/**
		 * Main Woo_Instant_Notifications Instance
		 *
		 * Ensures only one instance of WooCommerce_Instant_Notifications is loaded or can be loaded.
		 *
		 * @since 0.1
		 * @static
		 * @see Woo_Instant_Notifications()
		 * @return Woo_Instant_Notifications - Main instance
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * WooCommerce_Instant_Notifications Constructor.
		 */
		public function __construct() {
			$this->define_constants();
			$this->init_hooks();
			// Set up localisation.
			$this->load_plugin_textdomain();
		}

		/**
		 * Hook into actions and filters
		 * @since  0.1
		 */

		public function init_hooks() {
		
			add_action( 'init', array( $this, 'init' ) );
		}

		/**
		 * Define WCE Constants
		 */
		private function define_constants() {
			$this->define( 'WCNotifications_PLUGIN_FILE', __FILE__ );
			$this->define( 'WCNotifications_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			$this->define( 'WCNotifications_VERSION', $this->version );
			$this->define( 'WCNotifications_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
			$this->define( 'WCNotifications_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		/**
		 * Define constant if not already set
		 * @param  string $name
		 * @param  string|bool $value
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		public function includes() {
			include_once( 'admin/class-wcnotifications-admin.php' );
		}

		/**
		 * Init WooCommerce when WordPress Initialises.
		 */
		public function init() {
			$this->includes();
		}

		/**
		 * Get the plugin url.
		 * @return string
		 */
		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		/**
		 * Get the plugin path.
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

		/**
		 * Load Localisation files.
		 *
		 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
		 *
		 * Locales found in:
		 *      - WP_LANG_DIR/woo-instant-notifications/woo-instant-notifications-LOCALE.mo
		 *      - WP_LANG_DIR/plugins/woo-instant-notifications-LOCALE.mo
		 */
		public function load_plugin_textdomain() {
			$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
			$locale = apply_filters( 'plugin_locale', $locale, 'woo-instant-notifications' );

			unload_textdomain( 'woo-instant-notifications' );
			load_textdomain( 'woo-instant-notifications', WP_LANG_DIR . '/woo-instant-notifications/woo-instant-notifications-' . $locale . '.mo' );
			load_plugin_textdomain( 'woo-instant-notifications', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
		}

	}

	/**
	 * Returns the main instance of Woo_Instant_Notifications to prevent the need to use globals.
	 *
	 * @since  0.1
	 * @return Woo_Instant_Notifications
	 */
	function Woo_Instant_Notifications() {
		return Woo_Instant_Notifications::instance();
	}

	Woo_Instant_Notifications();
}
