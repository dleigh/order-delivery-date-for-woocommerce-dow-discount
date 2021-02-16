<?php
/**
 * Plugin Name: Order Delivery Date for WooCommerce Day of Week Discount
 * Plugin URI: https://david.leighweb.com
 * Description: This plugin extends the Order Delivery Date Lite/Pro for WooCommerce plugin to
 *            enable the store owner to give discounts by the day of the week of the
 *            date selected using the Order Delivery Date plugin
 * Author: David Leigh
 * Version: 1.3.6
 * Author URI: https://david.leighweb.com
 * Text Domain: woo-dowd
 * Requires PHP: 5.6
 * WC requires at least: 3.0.0
 * WC tested up to: 4.9.2
 * GitHub Plugin URI: dleigh/order-delivery-date-for-woocommerce-dow-discount
 * GitHub Plugin URI: https://github.com/dleigh/order-delivery-date-for-woocommerce-dow-discount
 *
 * @package  Order-Delivery-Date-for-WooCommerce-DOW-Discount
 */

/**
 * Latest version of the plugin
 *
 * @since 1.0
 */
$woo_dowd_version = '1.3.6';

/**
 * Include the require files
 *
 * @since 1.0
 */
require_once 'woo-dowd-config.php';
require_once 'class-woo-dowd-settings.php';

if ( ! class_exists( 'Order_Delivery_Date_Dow_Discount' ) ) {
	/**
	 * Main Order Delivery Date Dow Discount class
	 */
	class Order_Delivery_Date_Dow_Discount {

		/**
		 * Used in sending data back for ajax calls.
		 *
		 * @var array
		 */
		private $json_ajax_response_array = array();

		// private $iteration = 0; // used only for testing.
		/**
		 * Default Constructor
		 *
		 * @since 1.0
		 */
		public function __construct() {
			// Initialize settings.
			global $woocommerce;
			global $woo_dowd_orddd_active;

			register_activation_hook( __FILE__, array( $this, 'woo_dowd_activate' ) );

			add_action( 'init', array( $this, 'woo_dowd_update_po_file' ) );
			add_action( 'admin_init', array( $this, 'woo_dowd_capabilities' ) );
			add_action( 'init', array( $this, 'woo_dowd_check_if_orddd_lite_pro_active' ) );

			// Settings.
			add_action( 'admin_init', array( 'woo_dowd_settings', 'woo_dowd_dow_discount_admin_settings' ), 20 );

			// Admin settings styles.
			add_action( 'admin_enqueue_scripts', array( $this, 'woo_dowd_admin_style' ) );

			// Frontend scripts loading.
			// Cart page.
			if ( ( 'on' === get_option( 'orddd_lite_delivery_date_on_cart_page' ) ) ||
				( 'on' === get_option( 'orddd_delivery_date_on_cart_page' ) ) ) {
				add_action( 'woocommerce_cart_totals_before_shipping', array( $this, 'woo_dowd_front_cart_scripts_js' ) );
			}
			// Checkout page.
			if ( ( 'on' === get_option( 'orddd_lite_enable_delivery_date' ) ) ||
				( 'on' === get_option( 'orddd_enable_delivery_date' ) ) ) {
				add_action( 'woocommerce_review_order_before_shipping', array( $this, 'woo_dowd_front_checkout_scripts_js' ) );
			}

			// Ajax call setup.
			add_action( 'init', array( $this, 'woo_dowd_load_ajax' ) );

			// This is the filter that actually applies the discount.
			update_option( 'woocommerce_shipping_debug_mode', 'yes' ); // This is needed to make WooCommerce actually recalculate rates every time.
			add_filter( 'woocommerce_package_rates', array( $this, 'woo_dowd_apply_shipping_day_discount' ) );
		}

		/**
		 * Add default settings when plugin is activated for the first time
		 *
		 * @hook register_activation_hook
		 * @globals array $woo_dowd_weekdays Weekdays array
		 * @since 1.0
		 */
		public function woo_dowd_activate() {
			global $woo_dowd_weekdays;

			foreach ( $woo_dowd_weekdays as $n => $day_name ) {
				add_option( $n, 'checked' );
			}
			foreach ( $woo_dowd_weekdays as $n => $day_name ) {
				add_option( $n . '_date_start', '' );
				add_option( $n . '_date_end', '' );
				add_option( $n . '_discount_type', '' );
				add_option( $n . '_discount_amount', '' );
			}

			add_option( 'woo_dowd_product_ids', '' );
			add_option( 'woo_dowd_product_categories', '' );
			add_option( 'woo_dowd_product_ids_in_ex', 'include' );
			add_option( 'woo_dowd_product_categories_in_ex', 'include' );
			add_option( 'woo_dowd_enable_dow_discount', 'checked' );
			add_option( 'woo_dowd_enable_coupons_with_dow_discount', '' );
			add_option( 'woo_dowd_coupons_incompatible_message', __(
				'Note: Coupons and delivery date discounts cannot be used on the same order. Remove coupon to apply delivery date discount instead.',
				'woo-dowd-delivery-day-discount'
				)
			);
			add_option( 'woo_dowd_free_text', __( '(Free Shipping)', 'woo-dowd-delivery-day-discount' ) );
			add_option( 'woo_dowd_percentage_text', __( '(@amt@% discount)', 'woo-dowd-delivery-day-discount' ) );
			add_option( 'woo_dowd_amount_text', __( '(@amt@ off)', 'woo-dowd-delivery-day-discount' ) );
		}

		/**
		 * Load text domain for language translation
		 *
		 * @hook init
		 * @since 1.0
		 */
		public function woo_dowd_update_po_file() {
			$domain = 'woo-dowd';
			$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
			if ( ! load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '-' . $locale . '.mo' ) ) {
				load_plugin_textdomain( $domain, false, basename( dirname( __FILE__ ) ) . '/languages/' );
			}
		}

		/**
		 * Check if Order Delivery Date plugin is active or not. If it is not active then it will display a notice.
		 *
		 * @hook admin_init
		 * @since 1.0
		 */
		public function woo_dowd_check_if_orddd_lite_pro_active() {
			global $woo_dowd_tested_lite_plugin_version_pairs;
			global $woo_dowd_tested_pro_plugin_version_pairs;
			global $woo_dowd_orddd_active;
			if ( ! is_plugin_active( 'order-delivery-date-for-woocommerce/order_delivery_date.php' ) ) {
				if ( ! is_plugin_active( 'order-delivery-date/order_delivery_date.php' ) ) {
					deactivate_plugins( plugin_basename( __FILE__ ) );
					add_action( 'admin_notices', array( 'Order_Delivery_Date_Dow_Discount', 'woo_dowd_disabled_notice' ) );
					if ( isset( $_GET['activate'] ) ) {
						unset( $_GET['activate'] );
					}
				} else {
					$woo_dowd_orddd_active = 'pro';
					$current_version_pair  = $this->get_woo_dowd_version() . '-' . $this->woo_dowd_orddd_pro_get_version();
					if ( ! in_array( $current_version_pair, $woo_dowd_tested_pro_plugin_version_pairs, true ) ) {
						add_action( 'admin_notices', array( 'Order_Delivery_Date_Dow_Discount', 'woo_dowd_untested_pro_version_pair_notice' ) );
					}
				}
			} else {
				$woo_dowd_orddd_active = 'lite';
				$current_version_pair  = $this->get_woo_dowd_version() . '-' . $this->woo_dowd_orddd_lite_get_version();
				if ( ! in_array( $current_version_pair, $woo_dowd_tested_lite_plugin_version_pairs, true ) ) {
					add_action( 'admin_notices', array( 'Order_Delivery_Date_Dow_Discount', 'woo_dowd_untested_lite_version_pair_notice' ) );
				}
			}
		}

		/**
		 * Display a notice in the admin Plugins page if the plugin is activated while WooCommerce is deactivated.
		 *
		 * @hook admin_notices
		 * @since 1.0
		 */
		public static function woo_dowd_disabled_notice() {
			$class   = 'notice notice-error';
			$message = __( 'Order Delivery Date for WooCommerce Day of Week Discount plugin requires that Order Delivery Date for WooCommerce be installed and activated.', 'woo-dowd-delivery-day-discount' );

			printf( esc_html( '<div class="%1$s"><p>%2$s</p></div>' ), esc_html( $class ), esc_html( $message ) );
		}

		/**
		 * Display a notice in the admin Plugins page if the plugin has not been tested with this version of Order Delivery Date Day.
		 *
		 * @hook admin_notices
		 * @since 1.23
		 */
		public static function woo_dowd_untested_lite_version_pair_notice() {
			$class    = 'notice notice-warning is-dismissible';
			$message1 = __( 'The current version of the Order Delivery Date for WooCommerce Day of Week Discount plugin (', 'woo-dowd-delivery-day-discount' );
			$message2 = self::get_woo_dowd_version();
			$message3 = __( ') has NOT been tested with the current version of the Order Delivery Date Lite for WooCommerce plugin (', 'woo-dowd-delivery-day-discount' );
			$message4 = self::woo_dowd_orddd_lite_get_version();

			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_html( $class ), esc_html( $message1 . $message2 . $message3 . $message4 . ').' ) );
		}

		/**
		 * Display a notice in the admin Plugins page if the plugin has not been tested with this version of Order Delivery Date Day.
		 *
		 * @hook admin_notices
		 * @since 1.23
		 */
		public static function woo_dowd_untested_pro_version_pair_notice() {
			$class    = 'notice notice-warning is-dismissible';
			$message1 = __( 'The current version of the Order Delivery Date for WooCommerce Day of Week Discount plugin (', 'woo-dowd-delivery-day-discount' );
			$message2 = self::get_woo_dowd_version();
			$message3 = __( ') has NOT been tested with the current version of the Order Delivery Date Pro for WooCommerce plugin (', 'woo-dowd-delivery-day-discount' );
			$message4 = self::woo_dowd_orddd_pro_get_version();

			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_html( $class ), esc_html( $message1 . $message2 . $message3 . $message4 . ').' ) );
		}

		/**
		 * Returns the order delivery date plugin version number
		 *
		 * @return int $plugin_version Plugin Version
		 * @since 1.0
		 */
		public static function get_woo_dowd_version() {
			$plugin_data    = get_plugin_data( __FILE__ );
			$plugin_version = $plugin_data['Version'];
			return $plugin_version;
		}

		/**
		 * Capability to allow shop manager to edit settings
		 *
		 * @hook admin_init
		 * @since 1.0
		 */
		public function woo_dowd_capabilities() {
			$role = get_role( 'shop_manager' );
			if ( '' !== $role ) {
				$role->add_cap( 'manage_options' );
			}
		}

		/**
		 * Enqueue styles in the admin footer only for the settings page
		 *
		 * @param string $hook the WordPress admin page slug.
		 *
		 * @since 1.2.0
		 */
		public function woo_dowd_admin_style( $hook ) {
			global $woo_dowd_version;
			if ( ( 'toplevel_page_order_delivery_date_lite' !== $hook ) &&
				( 'toplevel_page_order_delivery_date' !== $hook ) ) {
				return;
			}

			wp_enqueue_style( 'woo-dowd-admin-style', esc_url( plugins_url( '/css/admin-style.css', __FILE__ ) ), '', $woo_dowd_version, false );
		}

		/**
		 * Enqueue scripts on the frontend cart page
		 *
		 * @since 1.0
		 */
		public function woo_dowd_front_cart_scripts_js() {
			global $woo_dowd_version;
			if ( 'on' === get_option( 'woo_dowd_enable_dow_discount' ) ) {

				wp_enqueue_script(
					'woo-dowd-cart-update-shipping',
					esc_url( plugins_url( '/js/woo-dowd-cart-update-shipping.js', __FILE__ ) ),
					array( 'jquery' ),
					$woo_dowd_version,
					false
				);

				wp_localize_script(
					'woo-dowd-cart-update-shipping',
					'woo_dowd_ajax_object',
					array(
						'ajax_url' => admin_url( 'admin-ajax.php' ),
						'nonce'    => wp_create_nonce( 'woo-dowd-cart-checkout-nonce' ),
					)
				);
			}
		}

		/**
		 * Enqueue scripts on the frontend checkout page
		 *
		 * @since 1.0
		 */
		public function woo_dowd_front_checkout_scripts_js() {
			global $woo_dowd_version;
			if ( 'on' === get_option( 'woo_dowd_enable_dow_discount' ) ) {

				wp_enqueue_script(
					'woo-dowd-checkout-update-shipping',
					esc_url( plugins_url( '/js/woo-dowd-checkout-update-shipping.js', __FILE__ ) ),
					array( 'jquery' ),
					$woo_dowd_version,
					false
				);

				wp_localize_script(
					'woo-dowd-checkout-update-shipping',
					'woo_dowd_ajax_object',
					array(
						'ajax_url' => admin_url( 'admin-ajax.php' ),
						'nonce'    => wp_create_nonce( 'woo-dowd-cart-checkout-nonce' ),
					)
				);
			}
		}

		/**
		 * Loads ajax callback to catch orddd_lite's ajax callback first to capture the newly modified/set date
		 *
		 * @hook init
		 * @since 1.2
		 */
		public function woo_dowd_load_ajax() {
			$handler = new self();
			if ( ! is_user_logged_in() ) {
				// This is only valid for orddd version 3.6.1 - otherwise it does nothing.
				add_action( 'wp_ajax_nopriv_orddd_lite_update_delivery_session', array( 'orddd_lite_process', 'woo_dowd_before_capture_orddd_lite_date' ), 5 );
				// This is valid starting with orddd version 3.8.1 - otherwise it does nothing.
				add_action( 'wp_ajax_nopriv_woo_dowd_delivery_date_capture', array( $handler, 'woo_dowd_delivery_date_capture' ) );
			} else {
				// This is only valid for orddd version 3.6.1 - otherwise it does nothing.
				add_action( 'wp_ajax_orddd_lite_update_delivery_session', array( 'orddd_lite_process', 'woo_dowd_before_capture_orddd_lite_date' ), 5 );
				// This is valid starting with orddd version 3.8.1 - otherwise it does nothing.
				add_action( 'wp_ajax_woo_dowd_delivery_date_capture', array( $handler, 'woo_dowd_delivery_date_capture' ) );
			}
		}

		/**
		 * This function receives the ajax call that provides the delivery date from the JQuery datepicker.
		 * NB - This is valid starting with orddd version 3.8.1 - otherwise it does nothing.
		 *
		 * Because of the way Woo is calling the actual calculation function later (multiple times) and because
		 * of Woo's and WordPress' use of caching, it's nearly impossible for this ajax call to get the date
		 * to the function executed by the filter hook.  The design desion was made to use transients instead of
		 * creating a separate table for this.  All other solutions (meta-data stored either in a post - which doesn't
		 * necessarily exist or in the Woo session or PHP session variables, etc.) have downsides or simply don't
		 * work as expected (caching again!).  It's not ideal but when you dive into the Woo architecture, there are
		 * many things that DON'T facilitate this sort of processing on shipping rates.  Consequently there have
		 * been significant hoops to jump through.
		 *
		 * @hook init
		 * @since 1.2
		 */
		public function woo_dowd_delivery_date_capture() {
			if ( check_ajax_referer( 'woo-dowd-cart-checkout-nonce', 'nonce', false ) ) {
				if ( isset( $_POST['deliverydate'] ) ) {
					$woo_dowd_delivery_date = sanitize_text_field( wp_unslash( $_POST['deliverydate'] ) );
					if ( WC()->session->has_session() ) {
						$session_cookie = WC()->session->get_session_cookie();
						delete_transient( 'woo_dowd_delivery_date_' . $session_cookie[0] );
						delete_transient( 'woo_dowd_iteration_' . $session_cookie[0] );
						delete_transient( 'woo_dowd_ajax_' . $session_cookie[0] );
						set_transient( 'woo_dowd_delivery_date_' . $session_cookie[0], $woo_dowd_delivery_date, HOUR_IN_SECONDS );
						set_transient( 'woo_dowd_ajax_' . $session_cookie[0], true, HOUR_IN_SECONDS );
						$this->json_ajax_response_array['cookie']  = serialize( WC()->session->get_session_cookie() );
						$this->json_ajax_response_array['message'] = 'Successfully sent and updated the delivery date: ' . get_transient( 'woo_dowd_delivery_date_' . $session_cookie[0] );
						wp_send_json_success( $this->json_ajax_response_array );
					} else {
						$error_array = array(
							'message' => 'No WooCommerce session found',
							'date'    => 'no date was able to be processed',
						);
						wp_send_json_error( $error_array );
					}
				} else {
					$error_array = array(
						'message' => 'No date passed from datepicker',
						'date'    => 'no date was able to be processed',
					);
					wp_send_json_error( $error_array );
				}
			} else {
				$error_array = array(
					'message' => 'nonce validation check failed',
					'date'    => 'no date was able to be processed',
				);
				wp_send_json_error( $error_array );
			}
			$error_array = array(
				'message' => 'should never have reached this code',
				'date'    => 'no date was able to be processed',
			);
			wp_send_json_error( $error_array );
		}

		/**
		 * Here's where we actually DO the work of the plugin!
		 *
		 * We're dependant on the format of the date stored by the Order Delivery Date Day plugin: d-m-y.
		 * Normally, strtotime() should handle it properly without any special processing.
		 *
		 * @globals The global WooCommerce object.
		 * @param array $rates is an array of shipping rates that WooCommerce gives us.
		 * @return array WooCommerce shipping rates array
		 *
		 * @since 1.0
		 */
		public function woo_dowd_apply_shipping_day_discount( $rates ) {
			if ( ! ( is_page( 'cart' ) || is_cart() ) && ! ( is_page( 'checkout' ) || is_checkout() ) ) {
				return $rates;
			}

			/* need our session cookie to find our transient data */
			$session_cookie = WC()->session->get_session_cookie();

			/**
			 * I'm sorry but this is the WEIRDEST thing I've ever seen.  First of all, in Woo, the refresh
			 * of the CART and the CHECKOUT pages are two different animals.  Not TOO strange but it means
			 * that the way this function is called (and HOW MANY TIMES it is called) varies between the two.
			 *
			 * The really weird part is that Woo will call this function MULTIPLE times within a given
			 * execution.  So either the page is refreshed or it's fired via ajax (Woo's and DOWD's) and
			 * either it's the CART or the CHECKOUT page.  In those different cases, this function is called
			 * multiple times.  In some of the calls the results DON'T GET SAVED to the Woo session (where
			 * the cart is stored) in the database and sometimes it does.  Can't figure out why.  Also it
			 * gets called a different number of times in different situations.  So the key was to figure
			 * out how many times it has been called and to actually do the calculations only ONCE and only
			 * on an iteration where the data is actually SAVED.
			 *
			 * I originally came up with specific "magic numbers" for a given installation but moving to
			 * another installation the checkout numbers didn't work.  Further testing in the new installation
			 * showed that "2" worked in every situation.  I then went back to the original environment and
			 * "2" worked there - regardless of how many times it was called, the "2" worked.  So I'm
			 * leaving it like that but also leaving the code just in case there is a situation where "2"
			 * does not work.
			 *
			 * I tested this via loading up a log file with the iteration numbers and when the calculation
			 * was done.  So here's some of the code - but you'll have to put it in the right places to do
			 * the actual test:
			 * $this->iteration++;
			 * $this->json_ajax_response_array['mydata'] = 'local iteration='.$this->iteration.' transient iteration='.$transient_iteration;
			 * error_log($this->json_ajax_response_array['mydata']);
			 * $this->json_ajax_response_array['mydata'] .= ' delivery date='.$woo_dowd_delivery_date;
			 * $this->json_ajax_response_array['mydata'] .= ' cost='.$rates[ $key ]->cost.' label='.$rates[ $key ]->label;
			 */
			if ( ( is_page( 'cart' ) || is_cart() ) ) {
				$calculation_iteration = 2;
			} 
			if ( ( is_page( 'checkout' ) || is_checkout() ) ) {
				if ( get_transient( 'woo_dowd_ajax_' . $session_cookie[0] ) ) {
					$calculation_iteration = 2;
				} else {
					$calculation_iteration = 2;
				}
			}

			/* Is it time to restart the count from zero? */
			if ( ! get_transient( 'woo_dowd_iteration_' . $session_cookie[0] ) ) {
				set_transient( 'woo_dowd_iteration_' . $session_cookie[0], 0, HOUR_IN_SECONDS );
				$transient_iteration = 0;
			} else {
				$transient_iteration = get_transient( 'woo_dowd_iteration_' . $session_cookie[0] );
				if ( $calculation_iteration < $transient_iteration ) {
					$transient_iteration = 0;
				}
			}
			/* Increment the transient iteration counter and get out if it's not time to really do something. */
			$transient_iteration++;
			set_transient( 'woo_dowd_iteration_' . $session_cookie[0], $transient_iteration, HOUR_IN_SECONDS );
			if ( $calculation_iteration > $transient_iteration ) {
				return $rates;
			}

			/* set the day of the week from the date and get out (error) if there is no date. */
			$woo_dowd_delivery_dow  = '';
			$woo_dowd_delivery_date = get_transient( 'woo_dowd_delivery_date_' . $session_cookie[0] );
			if ( $woo_dowd_delivery_date ) {
				$woo_dowd_delivery_dow = date( 'w', strtotime( $woo_dowd_delivery_date ) );
			} else {
				return $rates;
			}

			/* make sure the day of week delivery discount is actually enabled */
			$dowd_enabled = get_option( 'woo_dowd_enable_dow_discount' );
			if ( ! $dowd_enabled ) {
				return $rates;
			}

			/* array is zero indexed and zero = Sunday.  4 values for each day of the week - 4x7. */
			$woo_dowd_delivery_day_array = array(
				array(
					'date-start'      => get_option( 'woo_dowd_weekday_0_date_start' ),   // Sunday.
					'date-end'        => get_option( 'woo_dowd_weekday_0_date_end' ),
					'discount-amount' => get_option( 'woo_dowd_weekday_0_discount_amount' ),
					'discount-type'   => get_option( 'woo_dowd_weekday_0_discount_type' ),
				),
				array(
					'date-start'      => get_option( 'woo_dowd_weekday_1_date_start' ),   // Monday.
					'date-end'        => get_option( 'woo_dowd_weekday_1_date_end' ),
					'discount-amount' => get_option( 'woo_dowd_weekday_1_discount_amount' ),
					'discount-type'   => get_option( 'woo_dowd_weekday_1_discount_type' ),
				),
				array(
					'date-start'      => get_option( 'woo_dowd_weekday_2_date_start' ),   // Tuesday.
					'date-end'        => get_option( 'woo_dowd_weekday_2_date_end' ),
					'discount-amount' => get_option( 'woo_dowd_weekday_2_discount_amount' ),
					'discount-type'   => get_option( 'woo_dowd_weekday_2_discount_type' ),
				),
				array(
					'date-start'      => get_option( 'woo_dowd_weekday_3_date_start' ),   // Wednesday.
					'date-end'        => get_option( 'woo_dowd_weekday_3_date_end' ),
					'discount-amount' => get_option( 'woo_dowd_weekday_3_discount_amount' ),
					'discount-type'   => get_option( 'woo_dowd_weekday_3_discount_type' ),
				),
				array(
					'date-start'      => get_option( 'woo_dowd_weekday_4_date_start' ),   // Thursday.
					'date-end'        => get_option( 'woo_dowd_weekday_4_date_end' ),
					'discount-amount' => get_option( 'woo_dowd_weekday_4_discount_amount' ),
					'discount-type'   => get_option( 'woo_dowd_weekday_4_discount_type' ),
				),
				array(
					'date-start'      => get_option( 'woo_dowd_weekday_5_date_start' ),   // Friday.
					'date-end'        => get_option( 'woo_dowd_weekday_5_date_end' ),
					'discount-amount' => get_option( 'woo_dowd_weekday_5_discount_amount' ),
					'discount-type'   => get_option( 'woo_dowd_weekday_5_discount_type' ),
				),
				array(
					'date-start'      => get_option( 'woo_dowd_weekday_6_date_start' ),   // Saturday.
					'date-end'        => get_option( 'woo_dowd_weekday_6_date_end' ),
					'discount-amount' => get_option( 'woo_dowd_weekday_6_discount_amount' ),
					'discount-type'   => get_option( 'woo_dowd_weekday_6_discount_type' ),
				),
			);

			/*
			* If we have no discount value for this day of the week
			* or if the date of this day is not within the start/end date window,
			* just return the rates unchanged.
			*/
			if ( ( empty( $woo_dowd_delivery_day_array[ $woo_dowd_delivery_dow ]['discount-amount'] ) ) ||
				( '0' === $woo_dowd_delivery_day_array[ $woo_dowd_delivery_dow ]['discount-amount'] ) ||
				( ( date( 'Y-m-d', strtotime( $woo_dowd_delivery_date ) ) < $woo_dowd_delivery_day_array[ $woo_dowd_delivery_dow ]['date-start'] ) &&
				! empty( $woo_dowd_delivery_day_array[ $woo_dowd_delivery_dow ]['date-start'] ) ) ||
				( ( date( 'Y-m-d', strtotime( $woo_dowd_delivery_date ) ) > $woo_dowd_delivery_day_array[ $woo_dowd_delivery_dow ]['date-end'] ) &&
				! empty( $woo_dowd_delivery_day_array[ $woo_dowd_delivery_dow ]['date-end'] ) )
			) {
				return $rates;
			}

			/*
			* If the items in the cart don't match eligible products or categories then
			* just return the rates unchanged. The intention of the following logic is
			* that product-id-level inclusion/exclusion is more granular than category-level
			* inclusion/exclusion.
			*/
			$ids_in_ex          = get_option( 'woo_dowd_product_ids_in_ex' );
			$categories_in_ex   = get_option( 'woo_dowd_product_categories_in_ex' );
			$product_ids        = get_option( 'woo_dowd_product_ids' );
			$product_categories = get_option( 'woo_dowd_product_categories' );
			if ( ! empty( $product_ids ) || ! empty( $product_categories ) ) {
				// Set our flag to be false until we find an eligible product.
				$eligible_for_discount = false;
				// Clean up the options fields.
				$product_ids        = str_replace( ',', ' ', $product_ids );
				$product_ids        = str_replace( '  ', ' ', $product_ids );
				$product_ids        = explode( ' ', $product_ids );
				$product_categories = str_replace( ',', ' ', $product_categories );
				$product_categories = str_replace( '  ', ' ', $product_categories );
				$product_categories = explode( ' ', $product_categories );
				// Check each cart item to see if it's eligible.
				foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
					if ( $cart_item['data']->get_parent_id() > 0 ) {  // need to see if this is a product with variations or not.
						$id_found       = in_array( $cart_item['data']->get_parent_id(), $product_ids, true );
						$category_found = has_term( $product_categories, 'product_cat', $cart_item['data']->get_parent_id() );
					} else {
						$id_found       = in_array( $cart_item['data']->get_id(), $product_ids, true );
						$category_found = has_term( $product_categories, 'product_cat', $cart_item['data']->get_id() );
					}
					if ( $category_found ) {                       // This product's category is in the options list.
						if ( 'include' === $categories_in_ex ) {     // The categories in the list are to be "included".
							if ( $id_found ) {                         // The product's id is in the options list.
								if ( 'include' === $ids_in_ex ) {        // The products in the list are to be "included".
									$eligible_for_discount = true;                    // We have an eligible product.
									break;                               // Stop searching.
								}
							} else {                                     // The product's id is not found in the list.
								$eligible_for_discount = true;                       // We have an eligible product.
								break;                                   // Stop searching.
							}
						} else {                                    // The categories in the list are to be "excluded".
							if ( $id_found ) {                        // The product's id is in the options list.
								if ( 'include' === $ids_in_ex ) {       // The products in the list are to be "included".
									$eligible_for_discount = true;                   // We have an eligible product.
									break;                              // Stop searching.
								}
							}
						}
					} else {                                        // This product's category is not found in the options list.
						if ( $id_found ) {                            // The product's id is in the options list.
							if ( 'include' === $ids_in_ex ) {           // The products in the list are to be "included".
								$eligible_for_discount = true;                      // We have an eligible product.
								break;                                  // Stop searching.
							}
						}
					}
				}
				// If no product is eligible then simply return the rates as is.
				if ( ! $eligible_for_discount ) {
					return $rates;
				}
			}

			/*
			* At this point we know that the user is eligible for a delivery date day of the week discount.
			* BUT now we need to deal with the issue of coupons.  A DOWD may not be compatible with a discount
			* based on the DOWD settings.  If it's not compatible with a coupon we need to check if there is already
			* a coupon and let the user know that coupons and delivery discounts are not compatible.
			*
			* Note: to make the message display only once and at the right time, we have to look at the iterations
			*       again just like with the rate calculation.  Otherwise, under certain circumstances, the message
			*       will display multiple times.
			*/
			$coupons         = WC()->cart->get_applied_coupons();
			$coupons_enabled = get_option( 'woo_dowd_enable_coupons_with_dow_discount' );
			if ( WC()->cart->get_applied_coupons() ) {
				if ( ! $coupons_enabled ) {
					if ( $transient_iteration === $calculation_iteration ) {
						$incompatible_message = get_option( 'woo_dowd_coupons_incompatible_message' );
						if ( ! in_array( $incompatible_message, wc_get_notices(), true ) ) {
							wc_add_notice( get_option( 'woo_dowd_coupons_incompatible_message' ) );
						}
					}
					return $rates;
				}
			}

				/*
			* Otherwise, do the caclulations on the rate table and return the rate table with the changes.
			*
			* Note: the "<span" markup that I've tried to put on this text appears to be stripped out by WooCommerce
			* as they don't want any non-text stuff in the labels.  It would appear to be some sanitizing on their
			* part, which is understandable but unfortunate.  I've left it in the code for the future when another
			* solution may be found and to let folks know that I tried to make that text styleable.
			*/
			$woo_dowd_discount_amount = $woo_dowd_delivery_day_array[ $woo_dowd_delivery_dow ]['discount-amount'];

			foreach ( $rates as $key => $rate ) {
				$original_cost = $rates[ $key ]->cost;
				if ( 0 === $original_cost ) {
					$original_cost = 1;
				}
				if ( 'percent' === $woo_dowd_delivery_day_array[ $woo_dowd_delivery_dow ]['discount-type'] ) {
					$rates[ $key ]->cost  = number_format( $rates[ $key ]->cost - ( $rates[ $key ]->cost * ( $woo_dowd_discount_amount / 100 ) ), 2 );
					$rates[ $key ]->label = $rates[ $key ]->label . '<span class="woo-dowd-pct-label"> ' . esc_html( str_replace( WOO_DOWD_VALUE_REPLACE, $woo_dowd_discount_amount, get_option( 'woo_dowd_percentage_text' ) ) ) . '</span>';
					$conversion_rate      = $rates[ $key ]->cost / $original_cost;
				} else {
					$rates[ $key ]->cost = number_format( $rates[ $key ]->cost - $woo_dowd_discount_amount, 2 );
					if ( $rates[ $key ]->cost <= 0 ) {
						$rates[ $key ]->cost  = 0;
						$rates[ $key ]->label = $rates[ $key ]->label . '<span class="woo-dowd-free-label"> ' . esc_html( get_option( 'woo_dowd_free_text' ) ) . '</span>';
						$conversion_rate      = 0;
					} else {
						$rates[ $key ]->label = $rates[ $key ]->label . '<span class="woo-dowd-amt-label"> ' . str_replace( WOO_DOWD_VALUE_REPLACE, wc_price( $woo_dowd_discount_amount ), esc_html( get_option( 'woo_dowd_amount_text' ) ) ) . '</span>';
						$conversion_rate      = $rates[ $key ]->cost / $original_cost;
					}
				}

				/*
				* Recalculate taxes for this shipping method.
				*/
				$current_user = wp_get_current_user();
				$taxes        = array();
				foreach ( $rates[ $key ]->taxes as $tax_key => $tax ) {
					if ( $tax > 0 ) { // Set the new tax cost.
							// Set the new line tax cost in the taxes array.
							$taxes[ $tax_key ] = number_format( $tax * $conversion_rate, 2 );
					}
				}
				// Set the new taxes costs.
				$rates[ $key ]->taxes = $taxes;
				// A little transient cleanup before we go.
				delete_transient( 'woo_dowd_iteration_' . $session_cookie[0] );
				delete_transient( 'woo_dowd_ajax_' . $session_cookie[0] );
				return $rates;
			}
		}

		/**
		 * This function returns the Order Delivery Date Lite for WooCommerce plugin version number.
		 *
		 * @return string Version of the plugin
		 * @since 1.0
		 */
		public static function woo_dowd_orddd_lite_get_version() {
			$plugin_version    = '';
			$orddd_plugin_dir  = dirname( dirname( __FILE__ ) );
			$orddd_plugin_dir .= '/order-delivery-date-for-woocommerce/order_delivery_date.php';

			$plugin_data = get_file_data( $orddd_plugin_dir, array( 'Version' => 'Version' ) );
			if ( ! empty( $plugin_data['Version'] ) ) {
				$plugin_version = $plugin_data['Version'];
			}
			return $plugin_version;
		}

		/**
		 * This function returns the Order Delivery Date Pro for WooCommerce plugin version number.
		 *
		 * @return string Version of the plugin
		 * @since 1.0
		 */
		public static function woo_dowd_orddd_pro_get_version() {
			$plugin_version    = '';
			$orddd_plugin_dir  = dirname( dirname( __FILE__ ) );
			$orddd_plugin_dir .= '/order-delivery-date/order_delivery_date.php';

			$plugin_data = get_file_data( $orddd_plugin_dir, array( 'Version' => 'Version' ) );
			if ( ! empty( $plugin_data['Version'] ) ) {
				$plugin_version = $plugin_data['Version'];
			}
			return $plugin_version;
		}
	}

	$order_delivery_date_dow_discount = new Order_Delivery_Date_Dow_Discount();
}
