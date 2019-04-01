<?php
/**
 * Order Delivery Date Day of Week Discount Uninstall
 *
 * Uninstalling Order Delivery Date for WooCommerce Day of Week Discount delets all settings for the plugin.
 *
 * @author   David Leigh
 * @package  Order-Delivery-Date-for-WooCommerce-DOW-Discount/Admin/Uninstaller
 * @version  1.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$woo_dowd_weekdays = array(
	'orddd_weekday_0' => __( 'Sunday', 'woo-dowd-delivery-day-discount' ),
	'orddd_weekday_1' => __( 'Monday', 'woo-dowd-delivery-day-discount' ),
	'orddd_weekday_2' => __( 'Tuesday', 'woo-dowd-delivery-day-discount' ),
	'orddd_weekday_3' => __( 'Wednesday', 'woo-dowd-delivery-day-discount' ),
	'orddd_weekday_4' => __( 'Thursday', 'woo-dowd-delivery-day-discount' ),
	'orddd_weekday_5' => __( 'Friday', 'woo-dowd-delivery-day-discount' ),
	'orddd_weekday_6' => __( 'Saturday', 'woo-dowd-delivery-day-discount' ),
);


foreach ( $woo_dowd_weekdays as $n => $day_name ) {
	delete_option(
		$n . '_date_start'
	);
	delete_option(
		$n . '_date_end'
	);
	delete_option(
		$n . '_discount_type'
	);
	delete_option(
		$n . '_discount_amount'
	);
}

delete_option(
	'woo_dowd_enable_dow_discount'
);

delete_option(
	'woo_dowd_free_text'
);

delete_option(
	'woo_dowd_percentage_text'
);

delete_option(
	'woo_dowd_amount_text'
);

delete_option(
	'woo_dowd_product_ids'
);

delete_option(
	'woo_dowd_product_categories'
);

delete_option(
	'woo_dowd_product_ids_in_ex'
);

delete_option(
	'woo_dowd_product_categories_in_ex'
);
