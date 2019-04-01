<?php
/**
 * Plugin Name: Order Delivery Date for WooCommerce Day of Week Discount
 * Plugin URI: https://david.leighweb.com
 * Description: This plugin extends the Order Delivery Date Lite for WooCommerce plugin to
 *              enable the store owner to give discounts by the day of the week of the
 *              date selected using the Order Delivery Date plugin
 * Author: David Leigh
 * Version: 1.1.0
 * Author URI: https://david.leighweb.com
 * Text Domain: woo-dowd
 * Requires PHP: 5.6
 * WC requires at least: 3.0.0
 * WC tested up to: 3.5.4
 *
 * @package  Order-Delivery-Date-for-WooCommerce-DOW-Discount
 */

/**
 * Latest version of the plugin
 *
 * @since 1.0
 */
$wpefield_version = '1.1.0';

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
		 * Default Constructor
		 *
		 * @since 1.0
		 */
		public function __construct() {
			// Initialize settings.
			register_activation_hook( __FILE__, array( $this, 'woo_dowd_activate' ) );

			add_action( 'init', array( $this, 'woo_dowd_update_po_file' ) );
			add_action( 'admin_init', array( $this, 'woo_dowd_capabilities' ) );
			add_action( 'admin_init', array( $this, 'woo_dowd_check_if_orddd_lite_active' ) );

			// Settings.
			add_action( 'admin_init', array( 'woo_dowd_settings', 'woo_dowd_dow_discount_admin_settings' ), 20 );

			// Frontend.
			if ( 'on' === get_option( 'orddd_lite_delivery_date_on_cart_page' ) ) {
				add_action( 'woocommerce_cart_totals_before_shipping', array( $this, 'woo_dowd_front_cart_scripts_js' ) );
			}
			if ( 'yes' === get_option( 'orddd_lite_delivery_date_on_checkout_page_enabled' ) ) {
				add_action( 'woocommerce_review_order_before_shipping', array( $this, 'woo_dowd_front_checkout_scripts_js' ) );
			}

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
		public function woo_dowd_check_if_orddd_lite_active() {
			global $woo_dowd_tested_plugin_version_pairs;
			if ( ! is_plugin_active( 'order-delivery-date-for-woocommerce/order_delivery_date.php' ) ) {
				deactivate_plugins( plugin_basename( __FILE__ ) );
				add_action( 'admin_notices', array( 'Order_Delivery_Date_Dow_Discount', 'woo_dowd_disabled_notice' ) );
				if ( isset( $_GET['activate'] ) ) {
					unset( $_GET['activate'] );
				}
			} else {
				$current_version_pair = $this->get_woo_dowd_version() . '-' . $this->woo_dowd_orddd_get_version();
				if ( ! in_array( $current_version_pair, $woo_dowd_tested_plugin_version_pairs, true ) ) {
					add_action( 'admin_notices', array( 'Order_Delivery_Date_Dow_Discount', 'woo_dowd_untested_version_pair_notice' ) );
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
		 * @since 1.0
		 */
		public function woo_dowd_untested_version_pair_notice() {
			$class    = 'notice notice-error';
			$message1 = __( 'This version of Order Delivery Date for WooCommerce Day of Week Discount plugin (', 'woo-dowd-delivery-day-discount' );
			$message2 = Order_Delivery_Date_Dow_Discount::get_woo_dowd_version();
			$message3 = __( ') has NOT been tested with this version of Order Delivery Date Lite for WooCommerce (', 'woo-dowd-delivery-day-discount' );
			$message4 = Order_Delivery_Date_Dow_Discount::woo_dowd_orddd_get_version();

			printf( esc_html( '<div class="%1$s"><p>%2$s</p></div>' ), esc_html( $class ), esc_html( $message1 . $message2 . $message3 . $message4 ) );
		}

		/**
		 * Returns the order delivery date plugin version number
		 *
		 * @return int $plugin_version Plugin Version
		 * @since 1.0
		 */
		public function get_woo_dowd_version() {
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
		 * Enqueue scripts in the admin footer
		 *
		 * @hook admin_footer
		 * @since 1.0
		 */
		public function admin_notices_scripts() {
			wp_enqueue_script(
				'woo-dowd-dismiss-notice.js',
				esc_url( plugins_url( '/js/woo-dowd-dismiss-notice.js', __FILE__ ) ),
				'',
				'',
				false
			);

			wp_enqueue_style( 'dismiss-notice', esc_url( plugins_url( '/css/dismiss-notice.css', __FILE__ ) ), '', '', false );
		}

		/**
		 * Enqueue scripts on the frontend cart page
		 *
		 * @since 1.0
		 */
		public function woo_dowd_front_cart_scripts_js() {
			global $wpefield_version;
			if ( 'on' === get_option( 'woo_dowd_enable_dow_discount' ) ) {

				wp_enqueue_script(
					'woo-dowd-cart-update-shipping.js',
					esc_url( plugins_url( '/js/woo-dowd-cart-update-shipping.js', __FILE__ ) ),
					array( 'jquery' ),
					'',
					false
				);
			}
		}

		/**
		 * Enqueue scripts on the frontend checkout page
		 *
		 * @since 1.0
		 */
		public function woo_dowd_front_checkout_scripts_js() {
			global $wpefield_version;
			if ( 'on' === get_option( 'woo_dowd_enable_dow_discount' ) ) {

				wp_enqueue_script(
					'woo-dowd-checkout-update-shipping.js',
					esc_url( plugins_url( '/js/woo-dowd-checkout-update-shipping.js', __FILE__ ) ),
					array( 'jquery' ),
					'',
					false
				);
			}
		}

		/**
		 * Here's where we actually DO the work of the plugin!
		 *
		 * We're dependant on the format of the date stored by the Order Delivery Date Day plugin: d-m-y.
		 * Normally, strtotime() should handle it properly without any special processing.
		 *
		 * @globals The global WooCommerce object.
		 * @param rates $rates is an array of shipping rates that WooCommerce gives us.
		 * @return array WooCommerce shipping rates array
		 *
		 * @since 1.0
		 */
		public function woo_dowd_apply_shipping_day_discount( $rates ) {
			global $woocommerce;
			$woo_dowd_delivery_date = WC()->session->get( 'h_deliverydate_lite' );
			$woo_dowd_delivery_dow  = date( 'w', strtotime( $woo_dowd_delivery_date ) );

			/* array is zero indexed and zero = Sunday.  4 values for each day of the week - 4x7. */
			$woo_dowd_delivery_day_array = array(
				array(
					'date-start'      => get_option( 'woo_dowd_weekday_0_date_start' ),      // Sunday.
					'date-end'        => get_option( 'woo_dowd_weekday_0_date_end' ),
					'discount-amount' => get_option( 'woo_dowd_weekday_0_discount_amount' ),
					'discount-type'   => get_option( 'woo_dowd_weekday_0_discount_type' ),
				),
				array(
					'date-start'      => get_option( 'woo_dowd_weekday_1_date_start' ),      // Monday.
					'date-end'        => get_option( 'woo_dowd_weekday_1_date_end' ),
					'discount-amount' => get_option( 'woo_dowd_weekday_1_discount_amount' ),
					'discount-type'   => get_option( 'woo_dowd_weekday_1_discount_type' ),
				),
				array(
					'date-start'      => get_option( 'woo_dowd_weekday_2_date_start' ),      // Tuesday.
					'date-end'        => get_option( 'woo_dowd_weekday_2_date_end' ),
					'discount-amount' => get_option( 'woo_dowd_weekday_2_discount_amount' ),
					'discount-type'   => get_option( 'woo_dowd_weekday_2_discount_type' ),
				),
				array(
					'date-start'      => get_option( 'woo_dowd_weekday_3_date_start' ),      // Wednesday.
					'date-end'        => get_option( 'woo_dowd_weekday_3_date_end' ),
					'discount-amount' => get_option( 'woo_dowd_weekday_3_discount_amount' ),
					'discount-type'   => get_option( 'woo_dowd_weekday_3_discount_type' ),
				),
				array(
					'date-start'      => get_option( 'woo_dowd_weekday_4_date_start' ),      // Thursday.
					'date-end'        => get_option( 'woo_dowd_weekday_4_date_end' ),
					'discount-amount' => get_option( 'woo_dowd_weekday_4_discount_amount' ),
					'discount-type'   => get_option( 'woo_dowd_weekday_4_discount_type' ),
				),
				array(
					'date-start'      => get_option( 'woo_dowd_weekday_5_date_start' ),      // Friday.
					'date-end'        => get_option( 'woo_dowd_weekday_5_date_end' ),
					'discount-amount' => get_option( 'woo_dowd_weekday_5_discount_amount' ),
					'discount-type'   => get_option( 'woo_dowd_weekday_5_discount_type' ),
				),
				array(
					'date-start'      => get_option( 'woo_dowd_weekday_6_date_start' ),      // Saturday.
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
					$id_found       = in_array( $cart_item['data']->get_id(), $product_ids, true );
					$category_found = has_term( $product_categories, 'product_cat', $cart_item['data']->get_id() );
					if ( $category_found ) {                        // This product's category is in the options list.
						if ( 'include' === $categories_in_ex ) {     // The categories in the list are to be "included".
							if ( $id_found ) {                          // The product's id is in the options list.
								if ( 'include' === $ids_in_ex ) {         // The products in the list are to be "included".
									$eligible_for_discount = true;                      // We have an eligible product.
									break;                                  // Stop searching.
								}
							} else {                                    // The product's id is not found in the list.
								$eligible_for_discount = true;                        // We have an eligible product.
								break;                                    // Stop searching.
							}
						} else {                                      // The categories in the list are to be "excluded".
							if ( $id_found ) {                          // The product's id is in the options list.
								if ( 'include' === $ids_in_ex ) {         // The products in the list are to be "included".
									$eligible_for_discount = true;                      // We have an eligible product.
									break;                                  // Stop searching.
								}
							}
						}
					} else {                                        // This product's category is not found in the options list.
						if ( $id_found ) {                            // The product's id is in the options list.
							if ( 'include' === $ids_in_ex ) {           // The products in the list are to be "included".
								$eligible_for_discount = true;                        // We have an eligible product.
								break;                                    // Stop searching.
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
					$rates[ $key ]->cost   = number_format( $rates[ $key ]->cost - ( $rates[ $key ]->cost * ( $woo_dowd_discount_amount / 100 ) ), 2 );
					$rates[ $key ]->label .= '<span class="woo-dowd-pct-label"> ' . esc_html( str_replace( WOO_DOWD_VALUE_REPLACE, $woo_dowd_discount_amount, get_option( 'woo_dowd_percentage_text' ) ) ) . '</span>';
					$conversion_rate       = $rates[ $key ]->cost / $original_cost;
				} else {
					$rates[ $key ]->cost = number_format( $rates[ $key ]->cost - $woo_dowd_discount_amount, 2 );
					if ( $rates[ $key ]->cost <= 0 ) {
						$rates[ $key ]->cost   = 0;
						$rates[ $key ]->label .= '<span class="woo-dowd-free-label"> ' . esc_html( get_option( 'woo_dowd_free_text' ) ) . '</span>';
						$conversion_rate       = 0;
					} else {
						$rates[ $key ]->label .= '<span class="woo-dowd-amt-label"> ' . str_replace( WOO_DOWD_VALUE_REPLACE, wc_price( $woo_dowd_discount_amount ), esc_html( get_option( 'woo_dowd_amount_text' ) ) ) . '</span>';
						$conversion_rate       = $rates[ $key ]->cost / $original_cost;
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

				return $rates;
			}
		}

		/**
		 * This function returns the Order Delivery Date Lite for WooCommerce plugin version number.
		 *
		 * @return string Version of the plugin
		 * @since 1.0
		 */
		public static function woo_dowd_orddd_get_version() {
			$plugin_version    = '';
			$orddd_plugin_dir  = dirname( dirname( __FILE__ ) );
			$orddd_plugin_dir .= '/order-delivery-date-for-woocommerce/order_delivery_date.php';

			$plugin_data = get_file_data( $orddd_plugin_dir, array( 'Version' => 'Version' ) );
			if ( ! empty( $plugin_data['Version'] ) ) {
				$plugin_version = $plugin_data['Version'];
			}
			return $plugin_version;
		}
	}
	$order_delivery_date_dow_discount = new Order_Delivery_Date_Dow_Discount();
}