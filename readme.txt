=== Order Delivery Date for WooCommerce Day of Week Discount ===
Contributors: David Leigh
Author URI: https://david.leighweb.com/
Plugin URI: https://github.com/dleigh/order-delivery-date-for-woocommerce-dow-discount
Tags: delivery date, order delivery date, woocommerce delivery date, delivery, order delivery, day of week, discount
Requires at least: ?
Tested up to: 5.1.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Order Delivery Date Day of Week Discount plugin allows the store owners who have the Order Delivery Date Lite plugin installed to specify certain days of the week where there is a discount. The plugin is fully integrated with WooCommerce. 

== Description ==

This plugin extends the Order Delivery Date Lite for WooCommerce plugin (https://wordpress.org/plugins/order-delivery-date-for-woocommerce/) to enable the store owner to give discounts by the day of the week of the date selected using the Order Delivery Date plugin. As such it depends on the Order Delivery Date Lite plugin (free). 

### This plugin allows you to do the following

* For a given day of the week, within a date range or exclusive of date range, specify a flat amount of discount on shipping
	* Specify specific text, with an amount placeholder (@amt@), that is placed next to the shipping amount. (e.g. "($10 off)") 
* For a given day of the week, within a date range or exclusive of date range, specify a discount percentage on shipping
	* Specify specific text, with an amount placeholder (@amt@), that is placed next to the shipping amount. (e.g. "(30% discount)") 
* When the discount makes the shipping free, you can add text of your choice to indicate that. (e.g. "(Free Shipping)")
* 1.1.0 You can specify a list of product ids and then whether to "include" or "exclude" those ids from the rest of the shipping discount rules
* 1.1.0 You can specify a list of catagories and then whether to "include" or "exclude" those catagories from the rest of the shipping discount rules

== Installation ==


== Frequently Asked Questions ==

= ? =

********

= ? =

********

== Changelog ==

= 1.2.0 (2019-05-13) =
* The parent plugin has evolved and changed the way that the date was stored.  They decided to store it locally on the client machine.  So This
  update was a major reworking of how that date gets to this plugin.  Ajax was added to accomplish this as well as transients to store data
  between our ajax call and the call to Woo's ajax to update the cart or checkout pages.  Additionally, much logic was added to account for the
  strange way that Woo behaves when invoking its shipping filter hook.
* The settings on the admin side were visually tidy-ed up a bit
* Some dead code was removed

= 1.1.0 (2019-03-18) =
* Added the ability to include/exclude specific posts and specific categories

= 1.0.0 (2019-03-07) =
* Initial release.

